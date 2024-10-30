<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelPromotion;

class HotelPromotionFactory extends Factory
{
    protected $model = HotelPromotion::class;

    public function definition()
    {
        return [
            'hotel_id' => Hotel::factory(),
            'promotion_name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'validity_start' => $this->faker->date,
            'validity_end' => $this->faker->date,
            'booking_start' => $this->faker->date,
            'booking_end' => $this->faker->date,
            'terms_conditions' => $this->faker->paragraph,
            'exclusions' => $this->faker->paragraph,
            ];
    }
}
