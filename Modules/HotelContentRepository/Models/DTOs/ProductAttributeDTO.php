<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductAttribute;

class ProductAttributeDTO
{
    public $id;

    public $product_id;

    public $config_attribute_id;

    public function __construct() {}

    public function transform(Collection $productAttributes)
    {
        return $productAttributes->map(function ($productAttribute) {
            return $this->transformAttribute($productAttribute);
        })->all();
    }

    public function transformAttribute(ProductAttribute $productAttribute)
    {
        return [
            'id' => $productAttribute->id,
            'config_attribute_id' => $productAttribute->config_attribute_id,
            'attribute' => $productAttribute->attribute->name,
        ];
    }
}
