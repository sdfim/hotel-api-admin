<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingItemCache;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingsMetadataRepository;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\API\BookingAPI\BookingApiHandlers\ComboBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHandlers\FlightBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHandlers\HotelBookingApiHandler;
use Modules\API\Requests\BookingAddItemHotelRequest;
use Modules\API\Requests\BookingRemoveItemHotelRequest;
use Modules\Enums\RouteBookingEnum;
use Modules\Enums\SupplierNameEnum;
use Modules\Enums\TypeRequestEnum;

class RouteBookingApiController extends Controller
{
    private ?string $type;

    private ?string $supplier;

    private ?string $route;

    public function __construct(
        private readonly HotelBookingApiHandler $hotelBookingApiHandler,
        private readonly FlightBookingApiHandler $flightBookingApiHandler,
        private readonly ComboBookingApiHandler $comboBookingApiHandler,
    ) {}

    public function handle(Request $request): mixed
    {
        $determinant = $this->determinant($request);
        if (! empty($determinant)) {
            return response()->json(['error' => $determinant['error']], 400);
        }
        if (! $this->isTypeValid($this->type)) {
            return response()->json(['error' => 'Invalid type'], 400);
        }
        if (! $this->isRouteValid($this->route)) {
            return response()->json(['error' => 'Invalid route'], 400);
        }
        if (is_null($this->supplier)) {
            return response()->json(['error' => 'Invalid supplier'], 400);
        }

        $dataHandler = match (TypeRequestEnum::from($this->type)) {
            TypeRequestEnum::HOTEL => $this->hotelBookingApiHandler,
            TypeRequestEnum::FLIGHT => $this->flightBookingApiHandler,
            TypeRequestEnum::COMBO => $this->comboBookingApiHandler,
        };

        return match ($this->route) {
            'addItem' => $dataHandler->addItem($this->addItemRequest($this->type), $this->supplier),
            'removeItem' => $dataHandler->removeItem($this->removeItemRequest($this->type), $this->supplier),
            default => response()->json(['error' => 'Invalid route'], 400),
        };
    }

    private function addItemRequest(string $type): Request
    {
        return match (TypeRequestEnum::from($type)) {
            TypeRequestEnum::HOTEL => resolve(BookingAddItemHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    private function removeItemRequest(string $type): Request
    {
        return match (TypeRequestEnum::from($type)) {
            TypeRequestEnum::HOTEL => resolve(BookingRemoveItemHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    private function determinant(Request $request): array
    {
        $this->type = $request->get('type') ?? null;
        $this->supplier = $request->get('supplier') ?? null;

        $requestTokenId = PersonalAccessToken::findToken($request->bearerToken())->id;

        // Autodetect type by booking_item and check Owner token
        if ($request->has('booking_item')) {
            if (! $this->validatedUuid('booking_item')) {
                return [];
            }

            $cacheBookingItem = Cache::get('room_combinations:'.$request->booking_item);

            $apiBookingItem = null;
            if (! $cacheBookingItem) {
                $waitTime = 0;
                $maxWaitTime = 10;
                while ($waitTime < $maxWaitTime) {
                    if ($request->route()->getName() === RouteBookingEnum::ROUTE_ADD_ITEM->value) {
                        $apiBookingItem = ApiBookingItemCache::where('booking_item', $request->booking_item)->with('search')->first();
                    } else {
                        $apiBookingItem = ApiBookingItem::where('booking_item', $request->booking_item)->with('search')->first();
                    }
                    if ($apiBookingItem) {
                        break;
                    }
                    \Log::debug('Waiting for booking_item to be available '.$waitTime.' s', ['request' => $request]);
                    sleep(1);
                    $waitTime++;
                }
                if (! $apiBookingItem) {
                    return ['error' => 'Unable to get booking_item within '.$maxWaitTime.' seconds'];
                }
            }

            $apiBookingItemCache = ApiBookingItemCache::where('booking_item', $request->booking_item)->with('search')->first();

            if (! $apiBookingItem && ! $cacheBookingItem && ! $apiBookingItemCache) {
                return ['error' => 'Invalid booking_item'];
            }
            if ($apiBookingItem) {
                $dbTokenId = $apiBookingItem->search->token_id;
                if ($dbTokenId !== $requestTokenId) {
                    return ['error' => 'Owner token not match'];
                }
                $this->supplier = Supplier::where('id', $apiBookingItem->supplier_id)->first()->name;
                $this->type = SearchRepository::geTypeBySearchId($apiBookingItem->search_id);
            }
            if ($apiBookingItemCache) {
                $dbTokenId = $apiBookingItemCache->search->token_id;
                if ($dbTokenId !== $requestTokenId) {
                    return ['error' => 'Owner token not match'];
                }
                $this->supplier = Supplier::where('id', $apiBookingItemCache->supplier_id)->first()->name;
                $this->type = SearchRepository::geTypeBySearchId($apiBookingItemCache->search_id);
            }
            if ($cacheBookingItem) {
                $this->type = TypeRequestEnum::HOTEL->value;
                $this->supplier = SupplierNameEnum::HBSI->value;
            }
        }

        // Autodetect type and supplier by booking_id and check Owner token
        if ($request->has('booking_id')) {
            if (! $this->validatedUuid('booking_id')) {
                return ['error' => 'Invalid booking_id'];
            }
            $bi = BookingRepository::geTypeSupplierByBookingId($request->get('booking_id'));

            if (empty($bi)) {
                /**
                 * This logic was added to support imported bookings from TravelTek
                 */
                $bi = ApiBookingsMetadataRepository::geTypeSupplierByBookingId($request->get('booking_id'));

                if (empty($bi)) {
                    return ['error' => 'Invalid booking_id'];
                } else {
                    $bi['token_id'] = $requestTokenId;
                }
            }
            $dbTokenId = $bi['token_id'];
            if ($dbTokenId !== $requestTokenId) {
                return ['error' => 'Owner token not match'];
            }
            if ($this->type == null) {
                $this->type = $bi['type'];
            }
            if ($this->supplier == null) {
                $this->supplier = $bi['supplier'];
            }
        }

        // Autodetect type by search_id
        if ($request->has('search_id') && $this->type == null) {
            if (! $this->validatedUuid('search_id')) {
                return ['error' => 'Invalid search_id'];
            }
            $this->type = SearchRepository::geTypeBySearchId($request->get('search_id'));
        }

        $this->route = Route::currentRouteName();

        return [];
    }

    private function validatedUuid($id): bool
    {
        $validate = Validator::make(request()->all(), [$id => 'required|size:36']);
        if ($validate->fails()) {
            $this->type = null;
            $this->supplier = null;

            return false;
        }

        return true;
    }

    private function isTypeValid($value): bool
    {
        $values = array_map(function ($case) {
            return $case->value;
        }, TypeRequestEnum::cases());

        return in_array($value, $values, true);
    }

    private function isRouteValid($value): bool
    {
        $values = array_map(function ($case) {
            return $case->value;
        }, RouteBookingEnum::cases());

        return in_array($value, $values, true);
    }
}
