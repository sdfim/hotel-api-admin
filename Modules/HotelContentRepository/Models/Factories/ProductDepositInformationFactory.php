<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\DaysPriorTypeEnum;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDepositInformation;
use Modules\HotelContentRepository\Models\Hotel;

class ProductDepositInformationFactory extends Factory
{
    protected $model = ProductDepositInformation::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'days_prior_type' => DaysPriorTypeEnum::DEPARTURE->value,
            'days' => $this->faker->numberBetween(1, 30),
            'date' => null,
            'pricing_parameters' => $this->faker->randomElement(['per_channel', 'per_room', 'per_rate']),
            'pricing_value' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
