<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

class ProductCancellationPolicyDTO
{
    public $id;
    public $product_id;
    public $name;
    public $start_date;
    public $expiration_date;
    public $manipulable_price_type;
    public $price_value;
    public $price_value_type;
    public $price_value_target;

    public function __construct() {}

    public function transform(Collection $policies)
    {
        return $policies->map(function ($policy) {
            return $this->transformPolicy($policy);
        })->all();
    }

    public function transformPolicy(ProductCancellationPolicy $policy)
    {
        return [
            'id' => $policy->id,
            'name' => $policy->name,
            'start_date' => $policy->start_date,
            'expiration_date' => $policy->expiration_date,
            'manipulable_price_type' => $policy->manipulable_price_type,
            'price_value' => $policy->price_value,
            'price_value_type' => $policy->price_value_type,
            'price_value_target' => $policy->price_value_target,
            'conditions' => $policy->conditions->map(function ($condition) {
                return [
                    'id' => $condition->id,
                    'product_cancellation_policy_id' => $condition->product_cancellation_policy_id,
                    'field' => $condition->field,
                    'compare' => $condition->compare,
                    'value' => $condition->value,
                    'value_from' => $condition->value_from,
                    'value_to' => $condition->value_to,
                ];
            })->all(),
        ];
    }
}
