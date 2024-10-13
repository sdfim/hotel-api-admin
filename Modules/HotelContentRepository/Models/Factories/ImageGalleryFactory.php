<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ImageGallery;


class ImageGalleryFactory extends Factory
{
    protected $model = ImageGallery::class;

    public function definition()
    {
        return [
            'gallery_name' => $this->faker->word,
            'description' => $this->faker->optional()->paragraph,
        ];
    }
}
