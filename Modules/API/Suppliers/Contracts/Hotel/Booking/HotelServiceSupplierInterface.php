<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Booking;

use Modules\Enums\SupplierNameEnum;

interface HotelServiceSupplierInterface
{
    public function enrichmentRoomCombinations(array $input, array $filters, SupplierNameEnum $supplier): array;
}
