<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\AgeRestrictionTypeEnum;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;

class ProductAgeRestrictionFactory extends Factory
{
    protected $model = ProductAgeRestriction::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'restriction_type' => $this->faker->randomElement(AgeRestrictionTypeEnum::cases())->value,
            'value' => $this->faker->numberBetween(1, 18),
            'active' => $this->faker->boolean,
        ];
    }
}
