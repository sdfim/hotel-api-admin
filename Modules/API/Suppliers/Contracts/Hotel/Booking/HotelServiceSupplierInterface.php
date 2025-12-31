<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Booking;

interface HotelServiceSupplierInterface
{
    public function enrichmentRoomCombinations(array $input, array $filters): array;
}
