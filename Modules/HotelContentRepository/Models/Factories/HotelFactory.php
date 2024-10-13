<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ImageGallery;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'type' => $this->faker->word,
            'verified' => $this->faker->boolean,
            'direct_connection' => $this->faker->boolean,
            'manual_contract' => $this->faker->boolean,
            'commission_tracking' => $this->faker->boolean,
            'address' => $this->faker->address,
            'star_rating' => $this->faker->numberBetween(1, 5),
            'website' => $this->faker->url,
            'num_rooms' => $this->faker->numberBetween(1, 500),
            'featured' => $this->faker->boolean,
            'location' => $this->faker->address,
            'content_source' => $this->faker->randomElement(['IcePortal', 'Expedia', 'Internal']),
            'room_images_source' => $this->faker->randomElement(['IcePortal', 'Expedia', 'Internal']),
            'property_images_source' => $this->faker->randomElement(['IcePortal', 'Expedia', 'Internal']),
            'channel_management' => $this->faker->boolean,
            'hotel_board_basis' => $this->faker->word,
            'default_currency' => $this->faker->currencyCode,
        ];
    }
}
