<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class ProductDepositInformationDTO
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
        return $policies->map(function ($deposit) {
            return $this->transformDeposit($deposit);
        })->all();
    }

    public function transformDeposit(ProductDepositInformation $deposit)
    {
        return [
            'id' => $deposit->id,
            'name' => $deposit->name,
            'start_date' => $deposit->start_date,
            'expiration_date' => $deposit->expiration_date,
            'manipulable_price_type' => $deposit->manipulable_price_type,
            'price_value' => $deposit->price_value,
            'price_value_type' => $deposit->price_value_type,
            'price_value_target' => $deposit->price_value_target,
            'conditions' => $deposit->conditions->map(function ($condition) {
                return [
                    'id' => $condition->id,
                    'product_deposit_information_id' => $condition->product_deposit_information_id,
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
