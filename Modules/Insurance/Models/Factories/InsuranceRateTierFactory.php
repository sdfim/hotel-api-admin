<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRateTier;

class InsuranceRateTierFactory extends Factory
{
    protected $model = InsuranceRateTier::class;

    public function definition(): array
    {
        $minPrice = $this->faker->numberBetween(1000, 15000); // Random minimum price
        $maxPrice = $minPrice + $this->faker->numberBetween(1000, 15000); // Ensure max price is greater than min price

        return [
            'insurance_provider_id' => InsuranceProvider::factory(),
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'rate_type' => $this->faker->randomElement(['fixed', 'percentage']),
            'rate_value' => rand(20, 100) // Random insurance rate between 1% and 20%
        ];
    }
}
