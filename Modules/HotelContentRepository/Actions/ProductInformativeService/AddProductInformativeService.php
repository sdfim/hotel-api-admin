<?php

namespace Modules\HotelContentRepository\Actions\ProductInformativeService;

use Modules\HotelContentRepository\Models\ProductInformativeService;

class AddProductInformativeService
{
    public function createWithDynamicColumns(array $data): ProductInformativeService
    {
        $service = ProductInformativeService::create($data);
        $service->dynamicColumns()->createMany($data['dynamicColumns']);

        return $service;
    }
}
