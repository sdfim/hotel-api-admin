<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ContentSource;

class ContentSourceFactory extends Factory
{
    protected $model = ContentSource::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}
