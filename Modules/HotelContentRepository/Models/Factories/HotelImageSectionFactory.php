<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\HotelImageSection;

class HotelImageSectionFactory extends Factory
{
    protected $model = HotelImageSection::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
