<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Search;

/**
 * Interface for pricing suppliers
 * HBSI, Expedia, HotelTrader
 */
interface HotelPricingSupplierInterface extends HotelSupplierInterface
{
    public function price(array &$filters, array $searchInspector, array $preSearchData, string $hotelId = ''): ?array;

    public function processPriceResponse(
        array $rawResponse,
        array $filters,
        string $searchId,
        array $pricingRules,
        array $pricingExclusionRules,
        array $giataIds
    ): array;
}
