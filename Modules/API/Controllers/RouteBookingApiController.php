<?php

namespace Modules\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ApiBookingItem;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiSearchInspectorRepository as SearchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\API\BookingAPI\BookingApiHandlers\ComboBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHandlers\FlightBookingApiHandler;
use Modules\API\BookingAPI\BookingApiHandlers\HotelBookingApiHandler;
use Modules\API\Requests\BookingAddItemHotelRequest;
use Modules\API\Requests\BookingRemoveItemHotelRequest;
use Modules\Enums\RouteBookingEnum;
use Modules\Enums\TypeRequestEnum;

class RouteBookingApiController extends Controller
{
    /**
     * @var string|null
     */
    private string|null $type;

    /**
     * @var string|null
     */
    private string|null $supplier;

    /**
     * @var string|null
     */
    private string|null $route;

    /**
     * @param Request $request
     * @return mixed
     */
    public function handle(Request $request): mixed
    {
        $determinant = $this->determinant($request);
        if (!empty($determinant)) return response()->json(['message' => $determinant['error']], 400);
        if (!$this->isTypeValid($this->type)) return response()->json(['message' => 'Invalid type'], 400);
        if (!$this->isRouteValid($this->route)) return response()->json(['message' => 'Invalid route'], 400);
        if (is_null($this->supplier)) return response()->json(['message' => 'Invalid supplier'], 400);

        $dataHandler = match (TypeRequestEnum::from($this->type)) {
            TypeRequestEnum::HOTEL => new HotelBookingApiHandler(),
            TypeRequestEnum::FLIGHT => new FlightBookingApiHandler(),
            TypeRequestEnum::COMBO => new ComboBookingApiHandler(),
        };

        return match ($this->route) {
            'addItem' => $dataHandler->addItem($this->addItemRequest($this->type), $this->supplier),
            'removeItem' => $dataHandler->removeItem($this->removeItemRequest($this->type), $this->supplier),
            default => response()->json(['message' => 'Invalid route'], 400),
        };
    }

    /**
     * @param string $type
     * @return Request
     */
    private function addItemRequest(string $type): Request
    {
        return match (TypeRequestEnum::from($type)) {
            TypeRequestEnum::HOTEL => resolve(BookingAddItemHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    /**
     * @param string $type
     * @return Request
     */
    private function removeItemRequest(string $type): Request
    {
        return match (TypeRequestEnum::from($type)) {
            TypeRequestEnum::HOTEL => resolve(BookingRemoveItemHotelRequest::class),
            default => resolve(Request::class),
        };
    }

    /**
     * @param Request $request
     * @return array
     */
    private function determinant(Request $request): array
    {
        $this->type = $request->get('type') ?? null;
        $this->supplier = $request->get('supplier') ?? null;

        $requestTokenId = PersonalAccessToken::findToken($request->bearerToken())->id;

        # Autodetect type by booking_item and check Owner token
        if ($request->has('booking_item')) {
            if (!$this->validatedUuid('booking_item')) return [];
            $apiBookingItem = ApiBookingItem::where('booking_item', $request->get('booking_item'))->with('search')->first();
            if (!$apiBookingItem) return ['error' => 'Invalid booking_item'];
            $dbTokenId = $apiBookingItem->search->token_id;
            if ($dbTokenId !== $requestTokenId) return ['error' => 'Owner token not match'];
            $this->supplier = Supplier::where('id', $apiBookingItem->supplier_id)->first()->name;
            $this->type = SearchRepository::geTypeBySearchId($apiBookingItem->search_id);
        }

        # Autodetect type and supplier by booking_id and check Owner token
        if ($request->has('booking_id')) {
            if (!$this->validatedUuid('booking_id')) return ['error' => 'Invalid booking_id'];
            $bi = BookingRepository::geTypeSupplierByBookingId($request->get('booking_id'));
            if (empty($bi)) return ['error' => 'Invalid booking_id'];
            $dbTokenId = $bi['token_id'];
            if ($dbTokenId !== $requestTokenId) return ['error' => 'Owner token not match'];
            if ($this->type == null) $this->type = $bi['type'];
            if ($this->supplier == null) $this->supplier = $bi['supplier'];
        }

        # Autodetect type by search_id
        if ($request->has('search_id') && $this->type == null) {
            if (!$this->validatedUuid('search_id')) return ['error' => 'Invalid search_id'];
            $this->type = SearchRepository::geTypeBySearchId($request->get('search_id'));
        }

        $this->route = Route::currentRouteName();
        return [];
    }

    /**
     * @param $id
     * @return bool
     */
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

    /**
     * @param $value
     * @return bool
     */
    private function isTypeValid($value): bool
    {
        $values = array_map(function($case) {
            return $case->value;
        }, TypeRequestEnum::cases());
        return in_array($value, $values, true);
    }

    /**
     * @param $value
     * @return bool
     */
    private function isRouteValid($value): bool
    {
        $values = array_map(function($case) {
            return $case->value;
        }, RouteBookingEnum::cases());
        return in_array($value, $values, true);
    }
}
