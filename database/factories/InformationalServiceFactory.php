<?php

namespace Database\Factories;

use App\Models\ApiBookingItem;
use App\Models\InformationalService;
use Illuminate\Database\Eloquent\Factories\Factory;

class InformationalServiceFactory extends Factory
{
    protected $model = InformationalService::class;

    public function definition()
    {
        return [
            'booking_item' => ApiBookingItem::factory(), // Create a valid booking item reference
            'service_id' => $this->faker->randomNumber(),
            'cost' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
