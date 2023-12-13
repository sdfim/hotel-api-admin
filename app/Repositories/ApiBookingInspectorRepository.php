<?php

namespace App\Repositories;

use App\Models\ApiBookingInspector;
use Illuminate\Support\Facades\Storage;

class ApiBookingInspectorRepository
{
    /**
     * @param string $booking_id
     * @param string $booking_item
     * @param int $room_id
     * @return string|null
     */
    public static function getLinkDeleteItem(string $booking_id, string $booking_item, int $room_id): string | null
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('booking_item', $booking_item)
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        if (!isset($inspector)) {
            return null;
        }

        $json_response = json_decode(Storage::get($inspector->response_path));

        $rooms = $json_response->rooms;

        $linkDeleteItem = '';
        foreach ($rooms as $room) {
            if ($room->id == $room_id) {
                $linkDeleteItem = $room->links->cancel->href;
                break;
            }
        }

        return $linkDeleteItem;
    }

    /**
     * @param string $booking_id
     * @param int $room_id
     * @return string|null
     */
    public static function getLinkPutMethod(string $booking_id, int $room_id): string | null
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
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

    /**
     * @param $filters
     * @return string|null
     */
    public static function getItineraryId($filters): null | string
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->itinerary_id;
    }

    /**
     * @param $filters
     * @return string|null
     */
    public static function getSearchId($filters): null | string
    {
        $booking_id = $filters['booking_id'];

        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'retrieve' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        return $inspector->search_id;
    }

    /**
     * @param $booking_id
     * @return string|null
     */
    public function getLinkRetrieveItem($booking_id): string | null
    {
        $inspector = ApiBookingInspector::where('type', 'book')
            ->where('sub_type', 'like', 'create' . '%')
            ->where('booking_id', $booking_id)
            ->first();

        $json_response = json_decode(Storage::get($inspector->response_path));

        return $json_response->links->retrieve->href;
    }

    /**
     * @param $channel
     * @return array|null
     */
    public static function getAffiliateReferenceIdByChannel($channel): array | null
    {
        $inspectors = ApiBookingInspector::where('token_id', $channel)
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('type', 'book')
                        ->where('sub_type', 'like', 'retrieve' . '%');
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

    /**
     * @param string $booking_id
     * @return array
     */
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

    /**
     * @param string $booking_id
     * @param string $booking_item
     * @return bool
     */
    public static function isBook(string $booking_id, string $booking_item): bool
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('type', 'book')
            ->exists();
    }

    /**
     * @param string $booking_id
     * @param string $booking_item
     * @return bool
     */
    public static function isDuplicate(string $booking_id, string $booking_item): bool
    {
        return ApiBookingInspector::where('booking_item', $booking_item)
            ->where('booking_id', $booking_id)
            ->where('type', 'add_item')
            ->exists();
    }

    /**
     * @param string $booking_id
     * @return object
     */
    public static function bookedItems(string $booking_id): object
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->get();
    }

    /**
     * @param string $booking_id
     * @return object
     */
    public static function notBookedItems(string $booking_id): object
    {
        $itemsBooked = ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->get()
            ->pluck('booking_id')
            ->toArray();

        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'add_item')
            ->where('sub_type', 'like', 'price_check' . '%')
            ->whereNotIn('booking_id', $itemsBooked)
            ->get();
    }

    /**
     * @param string $booking_id
     * @param string $booking_item
     * @return object
     */
    public static function bookedItem(string $booking_id, string $booking_item): object
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->get();
    }

    /**
     * @param string $booking_id
     * @param string $booking_item
     * @return object|null
     */
    public static function getPassengers(string $booking_id, string $booking_item): object|null
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->where('type', 'add_passengers')
            ->first();
    }

    /**
     * @param string $booking_id
     * @return object|null
     */
    public static function getItemsInCart(string $booking_id): object|null
    {
        return ApiBookingInspector::where('booking_id', $booking_id)
            ->where('type', 'add_item')
            ->where('sub_type', 'like', 'price_check' . '%')
            ->get();
    }
}
