<?php

namespace App\Repositories;

use App\Models\ApiBookingsMetadata;

class ApiBookingsMetadataRepository
{
    public static function bookedItem(string $booking_id, string $booking_item): object
    {
        return ApiBookingsMetadata::where('booking_id', $booking_id)
            ->where('booking_item', $booking_item)
            ->get();
    }

    public static function bookedItems(string $booking_id): object
    {
        return ApiBookingsMetadata::where('booking_id', $booking_id)
            ->get();
    }

    public static function geTypeSupplierByBookingId(string $booking_id): array
    {
        $search = ApiBookingsMetadata::where('booking_id', $booking_id)->first();

        return $search ?
            [
                'type'      => 'hotel',
                'supplier'  => $search->supplier->name,
                'token_id'  => null,
            ] :
            [];
    }
}
