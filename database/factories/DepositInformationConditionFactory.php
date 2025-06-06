<?php

namespace Database\Factories;

use App\Models\DepositInformationCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepositInformationConditionFactory extends Factory
{
    protected $model = DepositInformationCondition::class;

    public function definition()
    {
        return [
            'deposit_information_id' => $this->faker->numberBetween(1, 100),
            'field' => $this->faker->word,
            'compare' => $this->faker->randomElement(['=', '!=', '>', '<', '>=', '<=', 'in', 'not_in']),
            'value' => $this->faker->optional()->word,
            'value_from' => $this->faker->optional()->word,
            'value_to' => $this->faker->optional()->word,
        ];
    }
}
