<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductFeeTax;
use Modules\Enums\FeeTaxCollectedByEnum;

class ProductFeeTaxDTO
{
    public $id;

    public $name;

    public $product_id;

    public $net_value;

    public $rack_value;

    public $currency;

    public $type;

    public $value_type;

    public $commissionable;

    public $collected_by;

    public $fee_category;

    public function __construct() {}

    public function transform(Collection $productFeeTaxes)
    {
        return $productFeeTaxes->map(function ($productFeeTax) {
            return $this->transformProductFeeTax($productFeeTax);
        })->all();
    }

    public function transformProductFeeTax(ProductFeeTax $productFeeTax)
    {
        return [
            'id' => $productFeeTax->id,
            'name' => $productFeeTax->name,
            'net_value' => $productFeeTax->net_value,
            'rack_value' => $productFeeTax->rack_value,
            'currency' => $productFeeTax->currency,
            'type' => $productFeeTax->type,
            'value_type' => $productFeeTax->value_type,
            'commissionable' => $productFeeTax->commissionable,
            'collected_by' => match ($productFeeTax->collected_by) {
                FeeTaxCollectedByEnum::DIRECT->value => 'direct',
                FeeTaxCollectedByEnum::VENDOR->value => 'vendor',
                default => is_string($productFeeTax->collected_by) ? strtolower($productFeeTax->collected_by) : $productFeeTax->collected_by,
            },
            'fee_category' => $productFeeTax->fee_category,
        ];
    }
}
