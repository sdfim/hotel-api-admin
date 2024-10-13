<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\KeyMapping;

class KeyMappingFactory extends Factory
{
    protected $model = KeyMapping::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'key_id' => $this->faker->uuid,
            'key_name' => $this->faker->randomElement(['UJV system', 'GIATA']),
        ];
    }
}
