<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductPromotion;

class ProductPromotionFactory extends Factory
{
    protected $model = ProductPromotion::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'promotion_name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'validity_start' => $this->faker->date,
            'validity_end' => $this->faker->date,
            'booking_start' => $this->faker->date,
            'booking_end' => $this->faker->date,
            'terms_conditions' => $this->faker->paragraph,
            'exclusions' => $this->faker->paragraph,
            'min_night_stay' => $this->faker->numberBetween(1, 5),
            'max_night_stay' => $this->faker->numberBetween(6, 10),
            ];
    }
}
