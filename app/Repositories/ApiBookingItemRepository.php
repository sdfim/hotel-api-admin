<?php

namespace App\Repositories;

use App\Models\ApiBookingItem;
use Illuminate\Support\Arr;
use Modules\Enums\ItemTypeEnum;

class ApiBookingItemRepository
{
    public static function isComlete(string $booking_item): bool
    {
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();

        return $bookingItem->rate_type === ItemTypeEnum::COMPLETE->value;
    }

    public static function isHas(string $booking_item): bool
    {
        return (bool) ApiBookingItem::where('booking_item', $booking_item);
    }

    public static function getSearchId(string $booking_item): string
    {
        return ApiBookingItem::where('booking_item', $booking_item)->first()->search_id;
    }

    public static function getItemData(string $booking_item): ?array
    {
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();

        return json_decode($bookingItem?->booking_item_data, true);
    }

    public static function getItemPricingData(string $booking_item): ?array
    {
        $bookingItem = ApiBookingItem::where('booking_item', $booking_item)->first();

        return json_decode($bookingItem?->booking_pricing_data, true);
    }

    public static function isNonRefundable(string $booking_item): bool
    {
        $item = self::getItemPricingData($booking_item);

        return Arr::get($item, 'non_refundable', false);
    }

    public static function getRateOccupancy(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['rate_occupancy'];
    }

    public static function getRateType(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['rate_type'];
    }

    public static function getRoomId(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['room_id'];
    }

    public static function getHotelId(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['hotel_id'];
    }

    public static function getBedGroups(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['bed_groups'];
    }

    public static function getRateOrdinal(string $booking_item): ?string
    {
        return self::getItemData($booking_item)['rate_ordinal'];
    }

    public static function getHotelSupplierId(string $booking_item): ?string
    {
        return Arr::get(self::getItemData($booking_item), 'hotel_supplier_id');
    }

    public static function getParentBookingItem(string $bookingItem): string
    {
        if (self::isComlete($bookingItem)) {
            $parentBookingItem = $bookingItem;
        } else {
            $parentBookingItem = ApiBookingItem::where('booking_item', $bookingItem)->first()->complete_id;
        }

        return $parentBookingItem;
    }
}
