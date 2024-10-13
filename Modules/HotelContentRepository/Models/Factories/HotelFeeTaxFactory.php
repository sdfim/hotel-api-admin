<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelFeeTax;

class HotelFeeTaxFactory extends Factory
{
    protected $model = HotelFeeTax::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => $this->faker->word,
            'net_value' => $this->faker->randomFloat(2, 10, 1000),
            'rack_value' => $this->faker->randomFloat(2, 10, 1000),
            'tax' => $this->faker->randomFloat(2, 0, 100),
            'type' => $this->faker->randomElement(['per_person', 'per_night', 'per_person_per_night']),
        ];
    }
}
