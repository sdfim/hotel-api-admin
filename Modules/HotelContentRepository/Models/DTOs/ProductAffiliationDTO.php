<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationDTO
{
    public $id;
    public $product_id;
    public $affiliation_name;
    public $combinable;

    public function __construct(ProductAffiliation $productAffiliation)
    {
        $this->id = $productAffiliation->id;
        $this->product_id = $productAffiliation->product_id;
        $this->affiliation_name = $productAffiliation->affiliation_name;
        $this->combinable = $productAffiliation->combinable;
    }
}
