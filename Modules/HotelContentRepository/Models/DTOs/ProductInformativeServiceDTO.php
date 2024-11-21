<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductInformativeService;

class ProductInformativeServiceDTO
{
    public $id;
    public $product_id;
    public $service_id;

    public function __construct(ProductInformativeService $informativeService)
    {
        $this->id = $informativeService->id;
        $this->product_id = $informativeService->product_id;
        $this->service_id = $informativeService->service_id;
    }
}
