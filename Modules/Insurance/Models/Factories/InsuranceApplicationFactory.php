<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\InsuranceApplication;
use Modules\Insurance\Models\InsurancePlan;

class InsuranceApplicationFactory extends Factory
{
    protected $model = InsuranceApplication::class;

    public function definition(): array
    {
        return [
            'insurance_plan_id' => InsurancePlan::factory(),
            'room_number' => rand(1, 3),
            'name' => $this->faker->name,
            'location' => $this->faker->city,
            'age' => $this->faker->numberBetween(18, 80),
            'applied_at' => $this->faker->dateTime,
            'total_insurance_cost_pp' => $this->faker->randomFloat(2, 50, 500),
        ];
    }
}
