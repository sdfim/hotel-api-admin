<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ProductCancellationPolicyCondition;

class ProductCancellationPolicyConditionFactory extends Factory
{
    protected $model = ProductCancellationPolicyCondition::class;

    public function definition()
    {
        return [
            'product_cancellation_policy_id' => $this->faker->numberBetween(1, 100),
            'field' => $this->faker->word,
            'compare' => $this->faker->randomElement(['=', '!=', '>', '<', '>=', '<=', 'in', 'not_in']),
            'value' => $this->faker->optional()->word,
            'value_from' => $this->faker->optional()->word,
            'value_to' => $this->faker->optional()->word,
        ];
    }
}
