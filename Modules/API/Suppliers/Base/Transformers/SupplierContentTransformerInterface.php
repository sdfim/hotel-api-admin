<?php

namespace Modules\API\Suppliers\Base\Transformers;

use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;

interface SupplierContentTransformerInterface
{
    /**
     * @return ContentSearchResponse[]
     */
    public function SupplierToContentSearchResponse(array $supplierResponse): array;
}
