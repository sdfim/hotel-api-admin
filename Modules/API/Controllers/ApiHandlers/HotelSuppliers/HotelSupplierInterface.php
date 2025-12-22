<?php

namespace Modules\API\Controllers\ApiHandlers\HotelSuppliers;

use Illuminate\Http\Request;

interface HotelSupplierInterface
{
    public function preSearchData(array &$filters, string $initiator): ?array;

    public function search(array $filters): array;

    public function detail(Request $request): array|object;

    public function price(array &$filters, array $searchInspector, array $preSearchData): ?array;

    public function processPriceResponse(
        array $rawResponse,
        array $filters,
        string $searchId,
        array $pricingRules,
        array $pricingExclusionRules,
        array $giataIds
    ): array;
}
