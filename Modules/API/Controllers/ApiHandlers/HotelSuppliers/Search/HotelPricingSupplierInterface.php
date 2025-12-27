<?php

namespace Modules\API\Controllers\ApiHandlers\HotelSuppliers\Search;

/**
 * Interface for pricing suppliers
 * HBSI, Expedia, HotelTrader
 */
interface HotelPricingSupplierInterface extends HotelSupplierInterface
{
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
