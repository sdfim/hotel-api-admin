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
                return [
                    'name' => $amenity->amenity->name,
                    'consortia' => $amenity->consortia,
                    'is_paid' => $amenity->is_paid ? 'Yes' : 'No',
                    'price' => $amenity->price,
                    'currency' => $amenity->currency,
                    'apply_type' => $amenity->apply_type,
                    'min_night_stay' => $amenity->min_night_stay,
                    'max_night_stay' => $amenity->max_night_stay,
                ];
            })->all(),
        ];
    }
}
