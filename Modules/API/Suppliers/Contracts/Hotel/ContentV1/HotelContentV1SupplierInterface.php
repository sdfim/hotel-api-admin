<?php

namespace Modules\API\Suppliers\Contracts\Hotel\ContentV1;

use Modules\Enums\SupplierNameEnum;

interface HotelContentV1SupplierInterface
{
    public function supplier(): SupplierNameEnum;

    public function getResults(array $giataCodes): array;

    public function getRoomsData(int $giataCode): array;
}
