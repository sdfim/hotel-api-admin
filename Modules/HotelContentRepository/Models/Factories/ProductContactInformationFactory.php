<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ProductContactInformation;
use Modules\HotelContentRepository\Models\Product;

class ProductContactInformationFactory extends Factory
{
    protected $model = ProductContactInformation::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
