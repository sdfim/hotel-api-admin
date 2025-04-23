<?php

namespace Modules\HotelContentRepository\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HotelContentRepository\Models\KeyMapping;
use Modules\HotelContentRepository\Models\KeyMappingOwner;
use Modules\HotelContentRepository\Models\Product;

class KeyMappingFactory extends Factory
{
    protected $model = KeyMapping::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'key_id' => $this->faker->uuid,
            'key_mapping_owner_id' => KeyMappingOwner::factory(),
        ];
    }
}
