<?php

namespace App\Repositories;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\ApiBookingItemCache;
use App\Models\ApiBookingsMetadata;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\API\Controllers\ApiHandlers\HotelApiHandler;
use Modules\Enums\InspectorStatusEnum;
use Modules\Enums\ItemTypeEnum;

class ApiBookingInspectorRepository
{
    public static function getLastBooked()
    {
        return ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->latest()
            ->first();
    }

    public static function getAllBookTestForCancel(string $supplierName): ?Collection
    {
        $supplierId = Supplier::where('name', $supplierName)->first()->id;

        $cancelledBookings = ApiBookingInspector::select('booking_id', 'booking_item')
            ->where('type', 'cancel_booking')
            ->where('sub_type', 'true')
            ->where(function ($query) {
                $query->where('status', '!=', 'error')
                    ->orWhere('status_describe', 'like', '%was not found%')
                    ->orWhere('status_describe', 'like', '%already cancelled%');
            })
            ->where('supplier_id', $supplierId)
            ->get();

        $pairs = [];
        foreach ($cancelledBookings as $cancelledBooking) {
            $pairs[] = ['booking_id' => $cancelledBooking->booking_id, 'booking_item' => $cancelledBooking->booking_item];
        }

        $bookedBookings = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', 'error')
            ->where('supplier_id', $supplierId)
            ->where(function ($query) use ($pairs) {
                foreach ($pairs as $pair) {
                    $query->where(function ($subQuery) use ($pair) {
                        $subQuery->where('booking_id', '!=', $pair['booking_id'])
                            ->orWhere('booking_item', '!=', $pair['booking_item']);
                    });
                }
            })
            ->get();

        $bookedItems = ApiBookingsMetadataRepository::bookedItemsByBookingIds($bookedBookings->pluck('booking_id')->toArray())
            ->pluck('booking_id')->toArray();

        $bookedBookings = $bookedBookings->filter(function ($bookedBooking) use ($bookedItems) {
            return in_array($bookedBooking->booking_id, $bookedItems);
        });

        return $bookedBookings;
    }

    public static function getLinkDeleteItem(string $booking_id, string $booking_item, int $room_id): array
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('booking_item', $booking_item)
            ->where('sub_type', 'like', 'retrieve'.'%')
            ->where('booking_id', $booking_id)
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();

        if (! isset($inspector)) {
            return [];
        }

        $json_response = json_decode(Storage::get($inspector->response_path));
        $rooms = $json_response->rooms;

        $linkDeleteItems = [];
        foreach ($rooms as $room) {
            if ($room->id == $room_id) {
                $linkDeleteItems[] = $room->links->cancel->href;
            }
        }

        return $linkDeleteItems;
    }

    public static function getLinkPutMethod(string $booking_id, string $booking_item, int $room_id): ?string
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve'.'%')
            ->where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));
        $rooms = $json_response->rooms;

        $linkPutMethod = '';
        foreach ($rooms as $room) {
            if ($room->id == $room_id) {
                $linkPutMethod = $room->links->change->href;
                break;
            }
        }

        return $linkPutMethod;
    }

    public static function getItineraryId($filters, $supplierId): ?string
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve'.'%')
            ->where('booking_id', $booking_id)
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->where('supplier_id', $supplierId)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->itinerary_id;
    }

    public static function getSearchId($filters): ?string
    {
        $booking_id = $filters['booking_id'];
        $booking_item = $filters['booking_item'];

        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve'.'%')
            ->where('sub_type', 'create')
            ->where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();

        return $inspector->search_id;
    }

    public function getLinkRetrieveItem($booking_id): ?string
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'create'.'%')
            ->where('booking_id', $booking_id)
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->links->retrieve->href;
    }

    public static function getAffiliateReferenceIdByChannel($channel, $filters = []): ?array
    {
        $inspectors = ApiBookingInspector::query()
            ->where('token_id', $channel)
            ->where('type', 'book')
            ->where('sub_type', 'like', 'retrieve%')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->when(filled(data_get($filters, 'api_client.id')), function ($q) use ($filters) {
                $q->where('request->api_client->id', (int) data_get($filters, 'api_client.id'));
            })
            ->when(filled(data_get($filters, 'api_client.email')), function ($q) use ($filters) {
                $q->where('request->api_client->email', data_get($filters, 'api_client.email'));
            })
            ->get();

        $list = [];
        foreach ($inspectors as $inspector) {
            $json_response = json_decode(Storage::get($inspector->response_path));
            if (isset($json_response->affiliate_reference_id)) {
                $list[] = [
                    'affiliate_reference_id' => $json_response->affiliate_reference_id,
                    'email' => $json_response->email,
                ];
            }
        }

        return $list;
    }

    public static function getBookedBookingIdsByChannel(int $supplier_id = 2): ?array
    {
        $token_id = ChannelRepository::getTokenId(request()->bearerToken());

        $inspectors = ApiBookingInspector::where('token_id', $token_id)
            ->where(function ($query) use ($supplier_id) {
                $query->where(function ($query) use ($supplier_id) {
                    $query->where('type', 'book')
                        ->where('sub_type', 'create')
                        ->where('supplier_id', $supplier_id)
                        ->where('status', '!=', InspectorStatusEnum::ERROR->value)
                        ->whereDate('created_at', '>=', now()->subDays(60));
                });
            })
            ->pluck('booking_id')
            ->unique()
            ->toArray();

        return $inspectors;
    }

    public static function geTypeSupplierByBookingId(string $booking_id): array
    {
        $search = ApiBookingInspector::where('booking_id', $booking_id)->first();

        return $search ?
            [
                'type' => $search->search_type,
                'supplier' => $search->supplier->name,
                'token_id' => $search->token_id,
            ] :
            [];
    }

    public static function isBook(string $booking_id, string $booking_item, bool $validateWithBookingInspector = true): bool
    {
        if (! $validateWithBookingInspector) {
            return ApiBookingsMetadata::where('booking_id', $booking_id)
                ->where('booking_item', $booking_item)
                ->exists();
        }

        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->exists();
    }

    public static function isBookByItem(string $booking_item): bool
    {
        return ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->exists();
    }

    public static function exists(string $booking_id, string $booking_item): bool
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->exists();
    }

    public static function isDuplicate(string $booking_id, string $booking_item): bool
    {
        return ApiBookingInspector::where('booking_item', $booking_item)
            ->where('booking_id', $booking_id)
            ->where('type', 'add_item')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->exists();
    }

    public static function bookedItems(string $booking_id): object
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->get();
    }

    public static function notBookedItems(string $booking_id): object
    {
        $itemsBooked = ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->get()
            ->pluck('booking_item')
            ->toArray();

        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'add_item')
            ->where(function ($query) {
                $query->where('sub_type', ItemTypeEnum::SINGLE->value)
                    ->orWhere('sub_type', ItemTypeEnum::COMPLETE->value)
                    ->orWhere('sub_type', 'like', 'price_check'.'%');
            })
            ->whereNotIn('booking_item', $itemsBooked)
            ->get();
    }

    public static function bookedBookingItems(string $booking_id): array
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->where('created_at', '>', Carbon::now()->subMinutes(HotelApiHandler::TTL))
            ->get()
            ->pluck('booking_item')
            ->toArray();
    }

    public static function getPriceBookingId(string $booking_id): ?float
    {
        $priceingData = 0;
        $items = self::bookedItems($booking_id)->pluck('booking_item')->toArray();
        foreach ($items as $item) {
            $itemPriced = ApiBookingItemRepository::getPricingData($item);
            $priceingData += $itemPriced['total_price'] ?? 0;
        }

        return $priceingData;
    }

    public static function bookedBookingItemsAll(): array
    {
        return ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->pluck('booking_item')
            ->toArray();
    }

    public static function bookedItem(string $booking_id, string $booking_item): object
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();
    }

    public static function getPassengers(string $booking_id, string $booking_item): ?object
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('type', 'add_passengers')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();
    }

    public static function getChangePassengers(string $bookingId, string $bookingItem): ApiBookingInspector
    {
        $changeQb = ApiBookingInspector::where('booking_id', $bookingId)
            ->where('booking_item', $bookingItem)
            ->where('type', 'change_passengers')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value);

        if ($changeQb->exists()) {
            return $changeQb->orderByDesc('created_at')->first();
        }

        return self::getPassengers($bookingId, $bookingItem);
    }

    public static function getItemsInCart(string $booking_id): ?object
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'add_item')
//            ->where('sub_type', 'like', 'price_check' . '%')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->get();
    }

    public static function isCancel(string $booking_item): bool
    {
        return ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'cancel_booking')
            ->where('sub_type', 'true')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->exists();
    }

    public static function getBookIdByBookingItem(string $booking_item): ?string
    {
        return ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'add_item')
            ->where('sub_type', 'complete')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first()
            ?->booking_id;
    }

    public static function getEmailVerificationBookingItem(string $booking_item): ?string
    {
        $request = ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'add_item')
            ->where('sub_type', 'complete')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first()
            ?->request;

        if (! $request) {
            return null;
        }

        $request = json_decode($request, true);

        return Arr::get($request, 'email_verification');
    }

    public static function getEmailAgentBookingItem(string $booking_item): ?string
    {
        $request = ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'add_item')
            ->where('sub_type', 'complete')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first()
            ?->request;

        if (! $request) {
            return null;
        }

        $request = json_decode($request, true);

        $apiClientId = Arr::get($request, 'api_client_id');
        $apiClientEmail = Arr::get($request, 'api_client_email');

        // Determine missing api client info from User model
        if (filled($apiClientId) && empty($apiClientEmail)) {
            $user = User::find($apiClientId);
            if ($user) {
                $apiClientEmail = $user->email;
            }
        }

        return $apiClientEmail;
    }

    public static function getBookItemsByBookingItem(string $booking_item): ?object
    {
        $bookingInspector = ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();

        if (! $bookingInspector) {
            return null;
        }

        return ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->first();
    }

    public static function hasAttachService(string $booking_item, array $service): bool
    {
        $inspector = ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'service_attach')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->get();

        foreach ($inspector as $item) {
            $addons_meta = $item->addons_meta;
            if ($addons_meta['type'] === 'informational_services') {
                foreach ($addons_meta['attributes'] as $attribute) {
                    if ($attribute['id'] === $service['id'] && $attribute['message'] === 'The service is attached') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function hasDetachService(string $booking_item, array $service): bool
    {
        $inspector = ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'service_detach')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->get();

        foreach ($inspector as $item) {
            $addons_meta = $item->addons_meta;
            if ($addons_meta['type'] === 'informational_services') {
                foreach ($addons_meta['attributes'] as $attribute) {
                    if ($attribute['id'] === $service['id'] && $attribute['message'] === 'The service is detached') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public static function getParams($request, $bookingItem = null): array
    {
        $bookingItem = $bookingItem ?? $request->input('booking_item');
        $apiBookingInspectorItem = ApiBookingInspectorRepository::isBookingItemInCart($bookingItem);

        if (! $apiBookingInspectorItem) {
            return [null, null, null, null];
        }

        $searchId = $apiBookingInspectorItem?->search_id;
        $apiSearchInspectorItem = ApiSearchInspectorRepository::getRequest($searchId);

        $bookingId = $apiBookingInspectorItem?->booking_id;

        $filters = $request->all();
        $filters['search_id'] = $searchId;

        $supplierId = Supplier::where('name', (string) $apiSearchInspectorItem['supplier'])->first()->id;

        return [$bookingId, $filters, $supplierId, $apiBookingInspectorItem];
    }

    public static function newBookingInspector(array $input): array
    {
        /**
         * @param  string  $booking_id
         * @param  array  $query
         * @param  array  $content
         * @param  array  $client_content
         * @param  int  $supplier_id
         * @param  string  $type
         * @param  string  $subType
         * @param  string  $search_type
         */
        [$booking_id, $query, $supplier_id, $type, $subType, $search_type] = $input;

        $token_id = ChannelRepository::getTokenId(request()->bearerToken());
        $booking_item = $query['booking_item'] ?? null;
        $search_id = $query['search_id'] ?? (
            $booking_item
                ? (ApiBookingItem::where('booking_item', $booking_item)->first()?->search_id
                ?? ApiBookingItemCache::where('booking_item', $booking_item)->first()?->search_id)
                : null
        );

        /** @var ApiBookingInspector $inspector */
        $inspector = app(ApiBookingInspector::class);
        $inspector->booking_id = $booking_id;
        $inspector->token_id = $token_id;
        $inspector->supplier_id = $supplier_id;
        $inspector->search_id = $search_id;
        $inspector->booking_item = $booking_item;
        $inspector->search_type = $search_type;
        $inspector->type = $type;
        $inspector->sub_type = $subType;
        $inspector->request = $query;

        Log::info('Created ApiBookingInspector:', ['inspector' => $inspector]);

        return $inspector->toArray();
    }

    public static function isBookingItemInCart(string $bookingItem): ?ApiBookingInspector
    {
        return ApiBookingInspector::where('type', 'add_item')
            ->where('booking_item', $bookingItem)
            ->where('status', 'success')
            ->first();
    }

    public static function getListQuoteFromInspector(): array
    {
        $tokenId = ChannelRepository::getTokenId(request()->bearerToken());
        $apiClientId = data_get(request()->all(), 'api_client_id');
        $apiClientEmail = data_get(request()->all(), 'api_client_email');
        // Determine missing api client info from User model
        if (filled($apiClientId) && empty($apiClientEmail)) {
            $user = User::find($apiClientId);
            if ($user) {
                $apiClientEmail = $user->email;
            }
        }
        if (filled($apiClientEmail) && empty($apiClientId)) {
            $user = User::where('email', $apiClientEmail)->first();
            if ($user) {
                $apiClientId = $user->id;
            }
        }

        $bookingDateFrom = request()->input('booking_date_from');
        $bookingDateTo = request()->input('booking_date_to');
        $page = (int) request()->input('page', 1);
        $resultsPerPage = (int) request()->input('results_per_page', 20);

        $bookedItems = ApiBookingInspectorRepository::bookedBookingItemsAll();

        $query = ApiBookingInspector::query()
            ->where('token_id', $tokenId)
            ->where('type', 'add_item')
            ->where('sub_type', 'complete')
            ->whereNotIn('booking_item', $bookedItems)
            ->when(filled($apiClientId) || filled($apiClientEmail), function ($q) use ($apiClientId, $apiClientEmail) {
                $q->where(function ($query) use ($apiClientId, $apiClientEmail) {
                    if (filled($apiClientId)) {
                        $query->orWhereJsonContains('request->api_client->id', (string) $apiClientId);
                    }
                    if (filled($apiClientEmail)) {
                        $query->orWhereJsonContains('request->api_client->email', (string) $apiClientEmail);
                    }
                });
            })
            ->when(filled($bookingDateFrom), function ($q) use ($bookingDateFrom) {
                $q->whereDate('created_at', '>=', $bookingDateFrom);
            })
            ->when(filled($bookingDateTo), function ($q) use ($bookingDateTo) {
                $q->whereDate('created_at', '<=', $bookingDateTo);
            })
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $booking_items = $query->skip(($page - 1) * $resultsPerPage)
            ->take($resultsPerPage)
            ->pluck('booking_item')
            ->toArray();

        return [
            'count' => $total,
            'page' => $page,
            'results_per_page' => $resultsPerPage,
            'booking_items' => $booking_items,
        ];
    }

    public static function getQuoteFromInspectorByBookingId(string $bookingItem): array
    {
        $tokenId = ChannelRepository::getTokenId(request()->bearerToken());
        $apiClientId = data_get(request()->all(), 'api_client_id');
        $apiClientEmail = data_get(request()->all(), 'api_client_email');

        return ApiBookingInspector::query()
            ->where('token_id', $tokenId)
            ->where('booking_item', $bookingItem)
            ->where('type', 'add_item')
            ->where('sub_type', 'complete')
            ->when(filled($apiClientId) || filled($apiClientEmail), function ($q) use ($apiClientId, $apiClientEmail) {
                $q->where(function ($query) use ($apiClientId, $apiClientEmail) {
                    if (filled($apiClientId)) {
                        $query->orWhereJsonContains('request->api_client->id', (string) $apiClientId);
                    }
                    if (filled($apiClientEmail)) {
                        $query->orWhereJsonContains('request->api_client->email', (string) $apiClientEmail);
                    }
                });
            })
            ->pluck('booking_item')
            ->toArray();
    }
}
