<?php

namespace Modules\API\Suppliers\Oracle\Adapters;

use App\Models\ApiBookingInspector;
use App\Models\ApiBookingsMetadata;
use Modules\API\Suppliers\Base\Adapters\BaseHotelBookingAdapter;
use Modules\API\Suppliers\Base\Traits\HotelBookingTrait;
use Modules\API\Suppliers\Contracts\Hotel\Booking\HotelBookingSupplierInterface;
use Modules\API\Suppliers\Oracle\Client\OracleClient;
use Modules\API\Tools\PricingRulesTools;
use Modules\Enums\SupplierNameEnum;

class OracleHotelBookingAdapter extends BaseHotelBookingAdapter implements HotelBookingSupplierInterface
{
    use HotelBookingTrait;

    public function __construct(
        private readonly OracleClient $client,
        private readonly OracleHotelAdapter $hotelAdapter,
        private readonly PricingRulesTools $pricingRulesService,
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

    // TODO: need to be refactored for multiple booking items
    public function priceCheck(array $filters): ?array
    {
        return [];
    }
}
