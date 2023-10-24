<?php

namespace Database\Factories;

use App\Models\GiataProperty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PropertyWeighting>
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
		$giataIds = GiataProperty::where('city', 'New York')->get()->pluck('code')->toArray();	

        return [
            'property' => $this->faker->randomElement($giataIds),
			'supplier_id' => 1,
			'weight' => $this->faker->numberBetween(1, 10000),
        ];
    }
}
