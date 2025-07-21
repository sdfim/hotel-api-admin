<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\HotelWebFinder;

class HotelWebFinderFactory extends Factory
{
    protected $model = HotelWebFinder::class;

    public function definition(): array
    {
        return [
            'base_url' => $this->faker->url(),
            'finder' => $this->faker->word(),
            'website' => $this->faker->word(),
            'example' => $this->faker->word(),
        ];
    }
}
