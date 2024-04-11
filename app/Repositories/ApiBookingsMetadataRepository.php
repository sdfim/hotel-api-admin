<?php

namespace App\Repositories;

use App\Models\ApiBookingsMetadata;

class ApiBookingsMetadataRepository
{

    /**
     * @param string $booking_id
     * @param string $booking_item
     * @return object
     */
    public static function bookedItem(string $booking_id, string $booking_item): object
    {
        return ApiBookingsMetadata::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->get();
    }

    /**
     * @param string $booking_id
     * @return object
     */
    public static function bookedItems(string $booking_id): object
    {
        return ApiBookingsMetadata::where('booking_id', $booking_id)
            ->get();
    }
}
