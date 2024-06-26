<?php

namespace Database\Factories;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservations>
 */
class ReservationFactory extends Factory
{
    use WithFaker;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $now = Carbon::now();

        $totalNet = $this->faker->randomFloat(2, 100, 3000);
        $totalTax = $this->faker->randomFloat(2, 30, 200);
        $totalFees = $this->faker->randomFloat(2, 10, 50);

        return [
            'date_offload' => $now->copy()->addDays(30),
            'date_travel' => $now->copy()->addDays(10),
            'passenger_surname' => $this->faker->lastName(),
            'reservation_contains' => json_encode([
                'type' => 'hotel',
                'supplier' => 'Expedia',
                'search_id' => Str::uuid(),
                'booking_item' => Str::uuid(),
                'booking_id' => Str::uuid(),
                'hotel_id' => $this->faker->numberBetween(1, 10000),
                'hotel_name' => $this->faker->text(),
                'price' => [
                    'currency' => 'USD',
                    'total_net' => $this->faker->randomFloat(2, 100, 3000),
                    'total_tax' => $this->faker->randomFloat(2, 30, 200),
                    'total_fees' => $this->faker->randomFloat(2, 10, 50),
                    'total_price' => $totalNet + $totalTax + $totalFees,
                    'giata_room_code' => '',
                    'giata_room_name' => '',
                    'supplier_room_name' => $this->faker->text(),
                    'per_day_rate_breakdown' => '',
                    'markup' => $this->faker->numberBetween(1, 200),
                ],
                'hotel_images' => json_encode([
                    $this->faker->imageUrl(),
                    $this->faker->imageUrl(),
                ]),
            ]),
            'channel_id' => 1,
            'total_cost' => $this->faker->randomFloat(2, 100, 3000),
            'canceled_at' => $now->copy(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
