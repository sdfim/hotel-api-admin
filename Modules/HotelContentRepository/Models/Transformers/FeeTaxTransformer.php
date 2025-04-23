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
            'type' => $tax->type,
            'value_type' => $tax->value_type,
            'commissionable' => $tax->commissionable,
            'collected_by' => $tax->collected_by,
            'fee_category' => $tax->fee_category,
            'apply_type' => $tax->apply_type,
        ];
    }
}
