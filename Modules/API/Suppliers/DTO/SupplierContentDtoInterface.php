<?php

namespace Modules\API\Suppliers\DTO;

use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;
interface SupplierContentDtoInterface
{
    /**
     * @param array $supplierResponse
     * @return ContentSearchResponse[]
     */
    public function SupplierToContentSearchResponse(array $supplierResponse): array;

}
