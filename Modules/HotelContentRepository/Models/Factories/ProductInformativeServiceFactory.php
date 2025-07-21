<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductInformativeService;

class ProductInformativeServiceFactory extends Factory
{
    protected $model = ProductInformativeService::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'service_id' => ConfigServiceType::factory(),
            'cost' => $this->faker->randomFloat(2, 0, 1000),
            'name' => $this->faker->name(),
            'currency' => $this->faker->currencyCode(),
            'service_time' => $this->faker->time(),
            'show_service_on_pdf' => $this->faker->boolean(),
            'show_service_data_on_pdf' => $this->faker->boolean(),
            'commissionable' => $this->faker->boolean(),
            'auto_book' => $this->faker->boolean(),
        ];
    }
}
