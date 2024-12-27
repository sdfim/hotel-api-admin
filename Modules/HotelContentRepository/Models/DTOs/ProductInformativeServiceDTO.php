<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductInformativeService;

class ProductInformativeServiceDTO
{
    public $id;
    public $product_id;
    public $service_id;

    public function __construct() {}

    public function transform(Collection $informativeServices)
    {
        return $informativeServices->map(function ($informativeService) {
            return $this->transformProductInformativeService($informativeService);
        })->all();
    }

    public function transformProductInformativeService(ProductInformativeService $informativeService)
    {
        return [
            'id' => $informativeService->id,
            'service_id' => $informativeService->service_id,
            'service' => $informativeService->service->name,
            'cost' => $informativeService->cost,
        ];
    }
}
