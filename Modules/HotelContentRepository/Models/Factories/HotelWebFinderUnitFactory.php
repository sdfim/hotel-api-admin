<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\HotelWebFinder;
use Modules\HotelContentRepository\Models\HotelWebFinderUnit;

class HotelWebFinderUnitFactory extends Factory
{
    protected $model = HotelWebFinderUnit::class;

    public function definition()
    {
        return [
            'web_finder_id' => HotelWebFinder::factory(),
            'field' => $this->faker->word,
            'value' => $this->faker->word,
        ];
    }
}
