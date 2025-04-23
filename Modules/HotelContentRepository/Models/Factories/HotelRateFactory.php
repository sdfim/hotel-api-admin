<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRate;

class HotelRateFactory extends Factory
{
    protected $model = HotelRate::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => $this->faker->word,
            'code' => $this->faker->word,
        ];
    }
}
