<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\ProductTypeEnum;
use Modules\HotelContentRepository\Models\ContentSource;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Models\Vendor;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'vendor_id' => Vendor::factory(),
            'product_type' => $this->faker->randomElement(ProductTypeEnum::cases()),
            'name' => $this->faker->word,
            'verified' => $this->faker->boolean,
            'content_source_id' => ContentSource::factory(),
            'property_images_source_id' => ContentSource::factory(),
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'default_currency' => $this->faker->currencyCode,
            'website' => $this->faker->url,
            'related_id' => Hotel::factory(),
            'related_type' => Hotel::class,
        ];
    }
}
