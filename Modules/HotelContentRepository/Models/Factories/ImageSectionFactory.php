<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ImageSection;

class ImageSectionFactory extends Factory
{
    protected $model = ImageSection::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
