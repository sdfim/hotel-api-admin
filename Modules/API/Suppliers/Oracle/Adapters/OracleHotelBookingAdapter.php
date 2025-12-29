<?php

namespace Modules\API\Suppliers\Oracle\Adapters;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingsMetadata;
use Modules\API\Suppliers\Base\Adapters\BaseHotelBookingAdapter;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelBookingSupplierInterface;
use Modules\Enums\SupplierNameEnum;

class OracleHotelBookingAdapter extends BaseHotelBookingAdapter implements HotelBookingSupplierInterface
{
    public function __construct(
    ) {}

    public function supplier(): SupplierNameEnum
    {
        return SupplierNameEnum::ORACLE;
    }

    public function book(array $filters, ApiBookingInspector $bookingInspector): ?array
    {
        return [];
    }

    public function retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, bool $isSync = false): ?array
    {
        return [];
    }

    public function cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, int $iterations = 0): ?array
    {
        return [];
    }

    public function listBookings(): ?array
    {
        return [];
    }

    public function changeBooking(array $filters, string $mode = 'soft'): ?array
    {
        return [];
    }

    public function availabilityChange(array $filters, $type = 'change'): ?array
    {
        return [];
    }

    // TODO: need to be refactored for multiple booking items
    public function priceCheck(array $filters): ?array
    {
        return [];
    }
}
