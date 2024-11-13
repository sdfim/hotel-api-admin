<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'weight' => $this->faker->numberBetween(1, 100),
            'verified' => $this->faker->boolean,
            'type' => $this->faker->randomElement([
                'Direct connection',
                'Manual contract',
                'Commission tracking',
            ]),
            'address' => $this->faker->address,
            'star_rating' => $this->faker->numberBetween(1, 5),
            'website' => $this->faker->url,
            'num_rooms' => $this->faker->numberBetween(1, 500),
            'location' => $this->faker->address,
            'content_source_id' => ContentSource::factory(),
            'room_images_source_id' => ContentSource::factory(),
            'property_images_source_id' => ContentSource::factory(),
            'channel_management' => $this->faker->boolean,
            'hotel_board_basis' => $this->faker->word,
            'default_currency' => $this->faker->currencyCode,
        ];
    }
}
