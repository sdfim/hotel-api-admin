<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductInformativeService;

class InformativeServiceTransformer extends TransformerAbstract
{
    public function transform(ProductInformativeService $service)
    {
        return [
            'name' => $service->service->name,
            'description' => $service->service->description,
            'cost' => $service->service->cost,
            'currency' => $service->currency,
            'service_time' => $service->service_time,
            'collected_by' => $service->collected_by,
        ];
    }
}
