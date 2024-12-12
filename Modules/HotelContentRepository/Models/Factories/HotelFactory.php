<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Property;
use Google\Service\AdExchangeBuyer\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\HotelSaleTypeEnum;
use Modules\Enums\MealPlansEnum;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition()
    {
        return [
            'giata_code' => Property::factory(),
            'weight' => $this->faker->numberBetween(1, 100),
            'featured_flag' => $this->faker->randomElement([1, 0]),
            'sale_type' => (string) $this->faker->randomElement([
                HotelSaleTypeEnum::DIRECT_CONNECTION->value,
                HotelSaleTypeEnum::MANUAL_CONTRACT->value,
                HotelSaleTypeEnum::COMMISSION_TRACKING->value,
            ]),
            'address' => (string) $this->faker->address,
            'star_rating' => $this->faker->numberBetween(1, 5),
            'num_rooms' => $this->faker->numberBetween(1, 500),
            'room_images_source_id' => ContentSource::factory(),
            'hotel_board_basis' => $this->faker->randomElement(MealPlansEnum::cases())->value,
            'travel_agent_commission' => $this->faker->randomFloat(2, 0, 20),
        ];
    }
}
