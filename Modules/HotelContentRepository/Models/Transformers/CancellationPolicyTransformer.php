<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

class CancellationPolicyTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'conditions',
    ];

    public function transform(ProductCancellationPolicy $cancellationPolicy)
    {
        return [
            'product_id' => $cancellationPolicy->product_id,
            'name' => $cancellationPolicy->name,
            'start_date' => $cancellationPolicy->start_date,
            'expiration_date' => $cancellationPolicy->expiration_date,
            'manipulable_price_type' => $cancellationPolicy->manipulable_price_type,
            'price_value' => $cancellationPolicy->price_value,
            'price_value_type' => $cancellationPolicy->price_value_type,
            'price_value_target' => $cancellationPolicy->price_value_target,
        ];
    }

    public function includeConditions(ProductCancellationPolicy $cancellationPolicy)
    {
        return $this->collection($cancellationPolicy->conditions, new CancellationPolicyConditionTransformer);
    }
}
