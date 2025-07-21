<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\AgeRestrictionTypeEnum;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class ProductAgeRestrictionFactory extends Factory
{
    protected $model = ProductAgeRestriction::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'restriction_type' => $this->faker->randomElement(AgeRestrictionTypeEnum::cases())->value,
            'value' => $this->faker->numberBetween(1, 18),
            'active' => $this->faker->boolean(),
        ];
    }
}
