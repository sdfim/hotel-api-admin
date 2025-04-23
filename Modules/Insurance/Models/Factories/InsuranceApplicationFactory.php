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
        $date = $this->faker->dateTime;

        return [
            'insurance_plan_id' => InsurancePlan::factory(),
            'room_number' => rand(1, 3),
            'name' => $this->faker->name,
            'location' => $this->faker->city,
            'age' => $this->faker->numberBetween(18, 80),
            'total_insurance_cost_pp' => $this->faker->randomFloat(2, 50, 500),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
