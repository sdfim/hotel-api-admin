<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class DepositInformationTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'conditions',
    ];

    public function transform(ProductDepositInformation $depositInformation)
    {
        return [
            'product_id' => $depositInformation->product_id,
            'name' => $depositInformation->name,
            'start_date' => $depositInformation->start_date,
            'expiration_date' => $depositInformation->expiration_date,
            'manipulable_price_type' => $depositInformation->manipulable_price_type,
            'price_value' => $depositInformation->price_value,
            'price_value_type' => $depositInformation->price_value_type,
            'price_value_target' => $depositInformation->price_value_target,
        ];
    }

    public function includeConditions(ProductDepositInformation $depositInformation)
    {
        return $this->collection($depositInformation->conditions, new DepositInformationConditionTransformer());
    }
}
