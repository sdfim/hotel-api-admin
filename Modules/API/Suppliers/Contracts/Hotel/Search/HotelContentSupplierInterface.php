<?php

namespace Modules\API\Suppliers\Contracts\Hotel\Search;

use Illuminate\Http\Request;

/**
 * Interface for content suppliers
 * IcePortal, Hilton, Expedia, HotelTrader
 */
interface HotelContentSupplierInterface extends HotelSupplierInterface
{
    /**
     * @deprecated This method is deprecated. For new implementations, return an empty array.
     */
    public function search(array $filters): array;

    /**
     * @deprecated This method is deprecated. For new implementations, return an empty array.
     */
    public function detail(Request $request): array|object;
}
