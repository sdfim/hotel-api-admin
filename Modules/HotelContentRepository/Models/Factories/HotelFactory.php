<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\HotelTypeEnum;
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
                HotelTypeEnum::DIRECT_CONNECTION->value,
                HotelTypeEnum::MANUAL_CONTRACT->value,
                HotelTypeEnum::COMMISSION_TRACKING->value,
            ]),
            'address' => $this->faker->address,
            'star_rating' => $this->faker->numberBetween(1, 5),
            'website' => $this->faker->url,
            'num_rooms' => $this->faker->numberBetween(1, 500),
            'location' => $this->faker->address,
            'content_source_id' => ContentSource::factory(),
            'room_images_source_id' => ContentSource::factory(),
            'property_images_source_id' => ContentSource::factory(),
            'travel_agent_commission' => $this->faker->numberBetween(1, 5),
            'hotel_board_basis' => $this->faker->word,
            'default_currency' => $this->faker->currencyCode,
        ];
    }
}
