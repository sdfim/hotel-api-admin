<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\MoveBookingItemCache;
use App\Jobs\SaveBookingInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;

class HbsiHotelBookingApiController extends BaseHotelBookingApiController
{
    public function addItem(array $filters): ?array
    {
        $booking_id = $filters['booking_id'] ?? (string) Str::uuid();

        $supplierId = Supplier::where('name', SupplierNameEnum::HBSI->value)->first()->id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, 'add_item', $filters['rate_type'], 'hotel',
        ]);
        $bookingItem = $filters['booking_item'];

        MoveBookingItemCache::dispatchSync($bookingItem);

        SaveBookingInspector::dispatchSync($bookingInspector);

        return ['booking_id' => $booking_id];
    }
}
