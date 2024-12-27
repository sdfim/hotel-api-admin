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
    public $details;

    public function __construct(
        private readonly ProductAffiliationDetailDTO $productAffiliationDetailDTO
    ) {}

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
            'affiliation_name' => $productAffiliation->affiliation_name,
            'combinable' => $productAffiliation->combinable,
            'details' => $this->productAffiliationDetailDTO->transform($productAffiliation->details),
        ];
    }
}
