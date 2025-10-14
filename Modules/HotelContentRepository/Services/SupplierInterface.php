<?php

namespace Modules\HotelContentRepository\Services;

interface SupplierInterface
{
    public function getResults(array $giataCodes): array;

    public function getRoomsData(int $giataCode): array;
}
