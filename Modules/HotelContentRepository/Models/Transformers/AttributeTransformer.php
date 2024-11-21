<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductAttribute;

class AttributeTransformer extends TransformerAbstract
{
    public function transform(ProductAttribute $attribute)
    {
        return [
            'name' => $attribute->name,
        ];
    }
}
