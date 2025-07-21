<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class ProductDepositInformationFactory extends Factory
{
    protected $model = ProductDepositInformation::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $expirationDate = $this->faker->dateTimeBetween($startDate, '+1 year');

        return [
            'product_id' => Product::factory(),
            'name' => $this->faker->word(),
            'start_date' => $startDate->format('Y-m-d'),
            'expiration_date' => $expirationDate->format('Y-m-d'),
            'manipulable_price_type' => $this->faker->randomElement(['total_price', 'net_price']),
            'price_value' => $this->faker->randomFloat(2, 0, 100),
            'price_value_type' => $this->faker->randomElement(['fixed_value', 'percentage']),
            'price_value_target' => $this->faker->randomElement(['per_person', 'per_room', 'per_night', 'not_applicable']),
        ];
    }
}
