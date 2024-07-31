<?php

namespace App\Repositories;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingItem;
use Illuminate\Support\Facades\Storage;
use Modules\Enums\InspectorStatusEnum;
use Modules\Enums\ItemTypeEnum;
use Modules\Enums\SupplierNameEnum;

class ApiBookingInspectorRepository
{
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

    public static function getLinkPutMethod(string $booking_id, string $booking_item, int $room_id): string|null
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

    public static function getItineraryId($filters): ?string
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve'.'%')
            ->where('booking_id', $booking_id)
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->itinerary_id;
    }

    public static function getSearchId($filters): ?string
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve'.'%')
            ->where('booking_id', $booking_id)
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

    public static function getAffiliateReferenceIdByChannel($channel): ?array
    {
        $inspectors = ApiBookingInspector::where('token_id', $channel)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('type', 'book')
                        ->where('sub_type', 'like', 'retrieve'.'%')
                        ->where('status', '!=', InspectorStatusEnum::ERROR->value);
                });
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

    public static function isBook(string $booking_id, string $booking_item): bool
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
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
            ->get()
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
            ->get();
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

    public static function getBookItemsByBookingItem(string $booking_item): ?object
    {
        $bookingInspector = ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value)
            ->first();

        if (!$bookingInspector) return null;

        return ApiBookingInspector::where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->first();
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

        $token_id = ChannelRenository::getTokenId(request()->bearerToken());
        $booking_item = $query['booking_item'] ?? null;
        $search_id = $query['search_id'] ?? ($booking_item
            ? ApiBookingItem::where('booking_item', $booking_item)->first()?->search_id
            : null);

        $inspector = new ApiBookingInspector();
        $inspector->booking_id = $booking_id;
        $inspector->token_id = $token_id;
        $inspector->supplier_id = $supplier_id;
        $inspector->search_id = $search_id;
        $inspector->booking_item = $booking_item;
        $inspector->search_type = $search_type;
        $inspector->type = $type;
        $inspector->sub_type = $subType;
        $inspector->request = $query;

        \Log::info('Created ApiBookingInspector:', ['inspector' => $inspector]);

        return $inspector->toArray();
    }

}
