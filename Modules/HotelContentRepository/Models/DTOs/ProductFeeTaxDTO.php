<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductFeeTax;

class ProductFeeTaxDTO
{
    public $id;
    public $name;
    public $product_id;
    public $net_value;
    public $rack_value;
    public $type;
    public $value_type;
    public $commissionable;
    public $collected_by;
    public $fee_category;

    public function __construct(ProductFeeTax $productFeeTax)
    {
        $this->id = $productFeeTax->id;
        $this->name = $productFeeTax->name;
        $this->product_id = $productFeeTax->product_id;
        $this->net_value = $productFeeTax->net_value;
        $this->rack_value = $productFeeTax->rack_value;
        $this->type = $productFeeTax->type;
        $this->value_type = $productFeeTax->value_type;
        $this->commissionable = $productFeeTax->commissionable;
        $this->collected_by = $productFeeTax->collected_by;
        $this->fee_category = $productFeeTax->fee_category;
    }
}
