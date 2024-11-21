<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductInformativeService;

class ProductInformativeServiceFactory extends Factory
{
    protected $model = ProductInformativeService::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'service_id' => ConfigServiceType::factory(),
        ];
    }
}
