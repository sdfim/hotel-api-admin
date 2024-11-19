<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\HotelAttribute;

class AttributeTransformer extends TransformerAbstract
{
    public function transform(HotelAttribute $attribute)
    {
        return [
            'name' => $attribute->name,
        ];
    }
}
