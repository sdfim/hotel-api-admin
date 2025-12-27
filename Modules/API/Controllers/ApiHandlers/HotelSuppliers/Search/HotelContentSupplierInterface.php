<?php

namespace Modules\API\Controllers\ApiHandlers\HotelSuppliers\Search;

use Illuminate\Http\Request;

/**
 * Interface for content suppliers
 * IcePortal, Hilton, Expedia, HotelTrader
 */
interface HotelContentSupplierInterface extends HotelSupplierInterface
{
    public function search(array $filters): array;

    public function detail(Request $request): array|object;
}
