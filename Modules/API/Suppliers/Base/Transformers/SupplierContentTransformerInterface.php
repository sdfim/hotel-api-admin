<?php

namespace Modules\API\Suppliers\Base\Transformers;

use Modules\API\ContentAPI\ResponseModels\ContentSearchResponse;
use Modules\Enums\SupplierNameEnum;

interface SupplierContentTransformerInterface
{
    public function supplier(): SupplierNameEnum;

    /**
     * @return ContentSearchResponse[]
     */
    public function SupplierToContentSearchResponse(array $supplierResponse): array;
}
