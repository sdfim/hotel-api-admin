<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationFactory extends Factory
{
    protected $model = ProductAffiliation::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'affiliation_name' => $this->faker->randomElement(['UJV Exclusive Amenities', 'Consortia Inclusions']),
            'combinable' => $this->faker->boolean,
        ];
    }
}
