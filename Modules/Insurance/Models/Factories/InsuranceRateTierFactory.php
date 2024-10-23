<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\InsuranceRateTier;

class InsuranceRateTierFactory extends Factory
{
    protected $model = InsuranceRateTier::class;

    public function definition(): array
    {
        $minPrice = $this->faker->numberBetween(1000, 15000); // Random minimum price
        $maxPrice = $minPrice + $this->faker->numberBetween(1000, 15000); // Ensure max price is greater than min price

        return [
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'insurance_rate' => $this->faker->randomFloat(2, 1, 20), // Random insurance rate between 1% and 20%
        ];
    }
}
