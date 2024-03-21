<?php

namespace App\Repositories;

use App\Models\ApiBookingItem;

class ApiBookingItemRepository
{
    /**
     * @param string $booking_item
     * @return array|null
     */
    public static function getItemData(string $booking_item): array|null
    {
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();
        return json_decode($bookingItem->booking_item_data, true);
    }

    public static function getRateOccupancy(string $booking_item): string|null
    {
        return self::getItemData($booking_item)['rate_occupancy'];
    }

    public static function getRateType(string $booking_item): string|null
    {
        return self::getItemData($booking_item)['rate_type'];
    }

    public static function getRoomId(string $booking_item): string|null
    {
        return self::getItemData($booking_item)['room_id'];
    }

    public static function getHotelId(string $booking_item): string|null
    {
        return self::getItemData($booking_item)['hotel_id'];
    }

    public static function getBedGroups(string $booking_item): string|null
    {
        return self::getItemData($booking_item)['bed_groups'];
    }

    public static function getRateOrdinal(string $booking_item): string|null
    {
        return self::getItemData($booking_item)['rate_ordinal'];
    }

    public static function getHotelSupplierId(string $booking_item): string|null
    {
        return self::getItemData($booking_item)['hotel_supplier_id'];
    }

}
