<?php

namespace Modules\API\Controllers\ApiHandlers\HotelSuppliers\Search;

/**
 * Base interface for all suppliers
 * Only common methods that are used everywhere
 */
interface HotelSupplierInterface
{
    /**
     * Used in both content and pricing
     */
    public function preSearchData(array &$filters, string $initiator): ?array;
}
