<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\HotelDepositInformation;
use Modules\HotelContentRepository\Models\Hotel;

class HotelDepositInformationFactory extends Factory
{
    protected $model = HotelDepositInformation::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'days_departure' => $this->faker->numberBetween(1, 30),
            'pricing_parameters' => $this->faker->randomElement(['per_channel', 'per_room', 'per_rate']),
            'pricing_value' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
