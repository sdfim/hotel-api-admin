<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\HotelImage;
use Modules\HotelContentRepository\Models\HotelImageSection;

class HotelImageFactory extends Factory
{
    protected $model = HotelImage::class;

    public function definition()
    {
        return [
            'image_url' => $this->faker->imageUrl,
            'tag' => $this->faker->word,
            'weight' => $this->faker->numberBetween(1, 100),
            'section_id' => HotelImageSection::factory(),
        ];
    }
}
