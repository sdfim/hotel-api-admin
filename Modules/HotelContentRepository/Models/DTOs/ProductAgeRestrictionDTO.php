<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class ProductAgeRestrictionDTO
{
    public $id;
    public $product_id;
    public $restriction_type;
    public $value;
    public $active;

    public function __construct(ProductAgeRestriction $productAgeRestriction)
    {
        $this->id = $productAgeRestriction->id;
        $this->product_id = $productAgeRestriction->product_id;
        $this->restriction_type = $productAgeRestriction->restriction_type;
        $this->value = $productAgeRestriction->value;
        $this->active = $productAgeRestriction->active;
    }
}
