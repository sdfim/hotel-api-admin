<?php

namespace Modules\API\BookingAPI\Controllers;

use App\Jobs\MoveBookingItemCache;
use App\Jobs\SaveBookingInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Enums\SupplierNameEnum;

class HotelTraderHotelBookingApiController extends BaseHotelBookingApiController
{
    public function addItem(array $filters, $type = 'add_item'): ?array
    {
        $booking_id = $filters['booking_id'] ?? (string) Str::uuid();

        $supplierId = Supplier::where('name', SupplierNameEnum::HOTEL_TRADER->value)->first()->id;
        $bookingInspector = ApiBookingInspectorRepository::newBookingInspector([
            $booking_id, $filters, $supplierId, $type, Arr::get($filters, 'rate_type', 'complete'), 'hotel',
        ]);
        $bookingItem = $filters['booking_item'];

        MoveBookingItemCache::dispatchSync($bookingItem);

        SaveBookingInspector::dispatchSync($bookingInspector);

        return ['booking_id' => $booking_id];
    }
}
