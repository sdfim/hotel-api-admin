<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
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
            'days_departure' => $this->faker->numberBetween(1, 30),
            'pricing_parameters' => $this->faker->randomElement(['per_channel', 'per_room', 'per_rate']),
            'pricing_value' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
