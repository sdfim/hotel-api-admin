<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class AffiliationTransformer extends TransformerAbstract
{
    public function transform(ProductAffiliation $affiliation)
    {
        return [
            'start_date' => $affiliation->start_date,
            'end_date' => $affiliation->end_date,
            'amenities' => $affiliation->amenities->map(function ($amenity) {
                return $amenity->name;
            })->all(),
            'consortia' => $affiliation->consortia,
            'is_paid' => $affiliation->is_paid ? 'Yes' : 'No',
            'price' => $affiliation->price,
        ];
    }
}
