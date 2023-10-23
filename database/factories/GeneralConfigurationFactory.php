<?php

namespace Database\Factories;

use App\Models\GeneralConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneralConfiguration>
 */
class GeneralConfigurationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GeneralConfiguration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'time_supplier_requests' => $this->faker->numberBetween(30, 120),
            'time_reservations_kept' => $this->faker->numberBetween(15, 60),
            'currently_suppliers' => $this->faker->numberBetween(5, 20),
            'time_inspector_retained' => $this->faker->numberBetween(5, 20),
            'star_ratings' => date('Y-m-d H:i:s'),
            'stop_bookings' => date('Y-m-d H:i:s'),
        ];
    }
}
