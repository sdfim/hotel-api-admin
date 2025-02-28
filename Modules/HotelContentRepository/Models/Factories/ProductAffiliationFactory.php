<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigAmenity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationFactory extends Factory
{
    protected $model = ProductAffiliation::class;

    public function definition()
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 year');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 year');

        return [
            'product_id' => Product::factory(),
            'amenity_id' => ConfigAmenity::factory(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];
    }
}
