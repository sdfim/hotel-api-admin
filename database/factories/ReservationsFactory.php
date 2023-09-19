<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Reservations;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservations>
 */
class ReservationsFactory extends Factory
{
    /**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Reservations::class;


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date_offload' => null,
			'date_travel' => \Carbon\Carbon::now()->subDays(30),
			'passenger_surname' => 'Passengersing',
			'total_cost' => 1240,
			'created_at' => \Carbon\Carbon::now(),
			'updated_at' => \Carbon\Carbon::now(),
        ];
    }
}
