<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationDTO
{
    public $id;
    public $product_id;
    public $affiliation_name;
    public $combinable;

    public function __construct() {}

    public function transform(Collection $productAffiliations)
    {
        return $productAffiliations->map(function ($productAffiliation) {
            return $this->transformAffiliation($productAffiliation);
        })->all();
    }

    public function transformAffiliation(ProductAffiliation $productAffiliation)
    {
        return [
            'id' => $productAffiliation->id,
            'product_id' => $productAffiliation->product_id,
            'affiliation_name' => $productAffiliation->affiliation_name,
            'combinable' => $productAffiliation->combinable,
        ];
    }
}
