<?php

namespace Modules\HotelContentRepository\Services;

interface SupplierInterface
{
    public function getResults(array $giataCodes): array;
}
