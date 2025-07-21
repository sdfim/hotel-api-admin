<?php

namespace Modules\HotelContentRepository\Models\Factories;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\ProductAttribute;

class ProductAttributeFactory extends Factory
{
    protected $model = ProductAttribute::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'config_attribute_id' => ConfigAttribute::factory(),
        ];
    }
}
