<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationDTO
{
    public $id;

    public $product_id;

    public $description;

    public $start_date;

    public $end_date;

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
            'consortia' => $productAffiliation->consortia?->name,
            'description' => $productAffiliation->description,
            'start_date' => $productAffiliation->start_date,
            'end_date' => $productAffiliation->end_date,
            'combinable' => $productAffiliation->combinable,
        ];
    }
}
