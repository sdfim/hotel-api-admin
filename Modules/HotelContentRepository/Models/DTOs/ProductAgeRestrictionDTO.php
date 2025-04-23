<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class ProductAgeRestrictionDTO
{
    public $id;
    public $product_id;
    public $restriction_type;
    public $value;
    public $active;

    public function __construct() {}

    public function transform(Collection $productAgeRestrictions)
    {
        return $productAgeRestrictions->map(function ($productAgeRestriction) {
            return $this->transformRestriction($productAgeRestriction);
        })->all();
    }

    public function transformRestriction(ProductAgeRestriction $productAgeRestriction)
    {
        return [
            'id' => $productAgeRestriction->id,
            'product_id' => $productAgeRestriction->product_id,
            'restriction_type' => $productAgeRestriction->restriction_type,
            'value' => $productAgeRestriction->value,
            'active' => $productAgeRestriction->active,
        ];
    }
}
