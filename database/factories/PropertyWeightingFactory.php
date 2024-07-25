<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingRules>
 */
class PropertyWeightingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $supplier = Supplier::factory()->create();

        return [
            'property' => $this->faker->numberBetween(1, 10000),
            'weight' => 1,
            'supplier_id' => $supplier->id,
        ];
    }
}
