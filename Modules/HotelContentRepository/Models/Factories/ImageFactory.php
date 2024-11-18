<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Image;
use Modules\HotelContentRepository\Models\ImageSection;

class ImageFactory extends Factory
{
    protected $model = Image::class;

    public function definition()
    {
        return [
            'image_url' => $this->faker->imageUrl,
            'tag' => $this->faker->word,
            'weight' => $this->faker->numberBetween(1, 100),
            'section_id' => ImageSection::factory(),
        ];
    }
}
