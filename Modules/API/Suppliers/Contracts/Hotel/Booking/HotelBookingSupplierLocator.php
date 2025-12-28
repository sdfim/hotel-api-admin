<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Booking;

use Exception;
use Modules\API\Suppliers\Expedia\Adapters\ExpediaHotelBookingAdapter;
use Modules\API\Suppliers\HBSI\Adapters\HbsiHotelBookingAdapter;
use Modules\API\Suppliers\HotelTrader\Adapters\HotelTraderHotelBookingAdapter;
use Modules\Enums\SupplierNameEnum;

class HotelBookingSupplierLocator
{
    private array $controllers;

    public function __construct(
        HbsiHotelBookingAdapter $hbsi,
        ExpediaHotelBookingAdapter $expedia,
        HotelTraderHotelBookingAdapter $hTrader,
    ) {
        $this->controllers = [
            SupplierNameEnum::HBSI->value => $hbsi,
            SupplierNameEnum::EXPEDIA->value => $expedia,
            SupplierNameEnum::HOTEL_TRADER->value => $hTrader,
        ];
    }

    public function getAdapter(SupplierNameEnum $supplierName): HotelBookingSupplierInterface
    {
        /* @var HotelBookingSupplierInterface $controller */
        if (! isset($this->controllers[$supplierName->value])) {
            throw new Exception("Unknown supplier: $supplierName->value");
        }

        return $this->controllers[$supplierName->value];
    }
}
