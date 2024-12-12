<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\FeeTaxCollectedByEnum;
use Modules\Enums\ProductFeeTaxApplyTypeEnum;
use Modules\Enums\ProductFeeTaxTypeEnum;
use Modules\Enums\ProductFeeTaxValueTypeEnum;
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
                ProductFeeTaxTypeEnum::TAX->value,
                ProductFeeTaxTypeEnum::FEE->value
            ]),
            'value_type' => $this->faker->randomElement([
                ProductFeeTaxValueTypeEnum::PERCENTAGE->value,
                ProductFeeTaxValueTypeEnum::AMOUNT->value
            ]),
            'collected_by' => $this->faker->randomElement([
                FeeTaxCollectedByEnum::DIRECT->value,
                FeeTaxCollectedByEnum::VENDOR->value
            ]),
            'commissionable' => $this->faker->boolean,
            'fee_category' => $this->faker->randomElement(['optional', 'mandatory']),
            'apply_type' => $this->faker->randomElement([
                ProductFeeTaxApplyTypeEnum::PER_NIGHT->value,
                ProductFeeTaxApplyTypeEnum::PER_PERSON->value
            ]),
        ];
    }
}
