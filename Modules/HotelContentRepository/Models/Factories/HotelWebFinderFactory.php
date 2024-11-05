<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelWebFinder;

class HotelWebFinderFactory extends Factory
{
    protected $model = HotelWebFinder::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'base_url' => $this->faker->url,
            'finder' => $this->faker->word,
            'type' => $this->faker->word,
        ];
    }
}
