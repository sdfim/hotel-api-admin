<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\Supplier;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;

class HbsiHotelBookingApiController extends BaseHotelBookingApiController
{
    /**
     * @param array $filters
     * @return array|null
     */
    public function addItem(array $filters): array|null
    {

        $booking_id = $filters['booking_id'] ?? (string)Str::uuid();

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        SaveBookingInspector::dispatch([
            $booking_id, $filters, [], [], $supplierId, 'add_item', $filters['rate_type'], 'hotel',
        ]);

        return ['booking_id' => $booking_id];
    }
}
