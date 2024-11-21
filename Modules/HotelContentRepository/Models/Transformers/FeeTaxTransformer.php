<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductFeeTax;

class FeeTaxTransformer extends TransformerAbstract
{
    public function transform(ProductFeeTax $tax)
    {
        return [
            'name' => $tax->name,
            'net_value' => $tax->net_value,
            'rack_value' => $tax->rack_value,
            'tax' => $tax->tax,
            'type' => $tax->type,
        ];
    }
}
