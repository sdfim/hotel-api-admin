<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\FeeTaxTypeEnum;
use Modules\Enums\FeeTaxValueTypeEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ProductFeeTax;
use Modules\HotelContentRepository\Models\Product;

class ProductFeeTaxFactory extends Factory
{
    protected $model = ProductFeeTax::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'name' => $this->faker->word,
            'net_value' => $this->faker->randomFloat(2, 10, 1000),
            'rack_value' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement([
                FeeTaxTypeEnum::TAX->value,
                FeeTaxTypeEnum::FEE->value
            ]),
            'value_type' => $this->faker->randomElement([
                FeeTaxValueTypeEnum::PERCENTAGE->value,
                FeeTaxValueTypeEnum::AMOUNT->value
            ]),
            'collected_by' => $this->faker->randomElement([
                FeeTaxCollectedByEnum::DIRECT->value,
                FeeTaxCollectedByEnum::VENDOR->value
            ]),
            'commissionable' => $this->faker->boolean,
            'fee_category' => $this->faker->randomElement(['optional', 'mandatory']),
        ];
    }
}
