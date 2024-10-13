<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;

class TravelAgencyCommissionFactory extends Factory
{
    protected $model = TravelAgencyCommission::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'consortium_id' => $this->faker->numberBetween(1, 100),
            'room_type' => $this->faker->word,
            'commission_value' => $this->faker->randomFloat(2, 0, 100),
            'date_range_start' => $this->faker->date,
            'date_range_end' => $this->faker->date,
        ];
    }
}
