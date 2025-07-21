<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigConsortium;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductConsortiaAmenity;

class ProductConsortiaAmenityFactory extends Factory
{
    protected $model = ProductConsortiaAmenity::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 year');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 year');

        return [
            'product_id' => Product::factory(),
            'rate_id' => null,
            'room_id' => null,
            'consortia_id' => ConfigConsortium::factory(),
            'description' => $this->faker->sentence(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];
    }
}
