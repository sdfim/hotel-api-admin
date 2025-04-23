<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductCancellationPolicyCondition;

class CancellationPolicyConditionTransformer extends TransformerAbstract
{
    public function transform(ProductCancellationPolicyCondition $condition)
    {
        return [
            'product_cancellation_policy_id' => $condition->product_cancellation_policy_id,
            'field' => $condition->field,
            'compare' => $condition->compare,
            'value' => $condition->value,
            'value_from' => $condition->value_from,
            'value_to' => $condition->value_to,
        ];
    }
}
