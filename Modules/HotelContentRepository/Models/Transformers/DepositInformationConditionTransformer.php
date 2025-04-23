<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductDepositInformationCondition;

class DepositInformationConditionTransformer extends TransformerAbstract
{
    public function transform(ProductDepositInformationCondition $condition)
    {
        return [
            'product_deposit_information_id' => $condition->product_deposit_information_id,
            'field' => $condition->field,
            'compare' => $condition->compare,
            'value' => $condition->value,
            'value_from' => $condition->value_from,
            'value_to' => $condition->value_to,
        ];
    }
}
