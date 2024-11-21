<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductAttribute;

class ProductAttributeDTO
{
    public $id;
    public $product_id;
    public $config_attribute_id;

    public function __construct(ProductAttribute $productAttribute)
    {
        $this->id = $productAttribute->id;
        $this->product_id = $productAttribute->product_id;
        $this->config_attribute_id = $productAttribute->config_attribute_id;
    }
}
