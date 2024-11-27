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
        return [
            'insurance_provider_id' => InsuranceProvider::factory(),
            'min_trip_cost' => $this->faker->randomFloat(2, 0, 10000),
            'max_trip_cost' => $this->faker->randomFloat(2, 10001, 20000),
            'consumer_plan_cost' => $this->faker->randomFloat(2, 10, 1000),
            'ujv_retention' => $this->faker->randomFloat(2, 1, 100),
            'net_to_trip_mate' => $this->faker->randomFloat(2, 1, 1000),
        ];
    }
}
