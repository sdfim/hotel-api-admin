<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAttribute;

class HotelAttributeFactory extends Factory
{
    protected $model = HotelAttribute::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => $this->faker->word,
            'attribute_value' => $this->faker->word,
        ];
    }
}
