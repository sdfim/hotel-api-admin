<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Google\Service\AdExchangeBuyer\Product;
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
            'weight' => $this->faker->numberBetween(1, 100),
            'sale_type' => $this->faker->randomElement([
                HotelTypeEnum::DIRECT_CONNECTION->value,
                HotelTypeEnum::MANUAL_CONTRACT->value,
                HotelTypeEnum::COMMISSION_TRACKING->value,
            ]),
            'address' => $this->faker->address,
            'star_rating' => $this->faker->numberBetween(1, 5),
            'num_rooms' => $this->faker->numberBetween(1, 500),
            'room_images_source_id' => ContentSource::factory(),
            'hotel_board_basis' => $this->faker->word,
            'travel_agent_commission' => $this->faker->randomFloat(2, 0, 20),
        ];
    }
}
