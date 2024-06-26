<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneralConfiguration>
 */
class GeneralConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'time_supplier_requests' => $this->faker->numberBetween(3, 120),
            'time_reservations_kept' => $this->faker->numberBetween(7, 365),
            'currently_suppliers' => json_encode(['1']),
            'time_inspector_retained' => $this->faker->numberBetween(60, 365),
            'star_ratings' => $this->faker->numberBetween(0, 5.5),
            'stop_bookings' => $this->faker->numberBetween(1, 365),
        ];
    }
}
