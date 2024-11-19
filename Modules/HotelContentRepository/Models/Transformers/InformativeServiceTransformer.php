<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\HotelInformativeService;

class InformativeServiceTransformer extends TransformerAbstract
{
    public function transform(HotelInformativeService $service)
    {
        return [
            'name' => $service->service->name,
            'description' => $service->service->description,
            'cost' => $service->service->cost,
        ];
    }
}
