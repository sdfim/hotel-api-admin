<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class AffiliationTransformer extends TransformerAbstract
{
    public function transform(ProductAffiliation $affiliation)
    {
        return [
            'affiliation_id' => $affiliation->affiliation_id,
            'consortia' => $affiliation->consortia->name,
            'description' => $affiliation->description,
            'start_date' => $affiliation->start_date,
            'end_date' => $affiliation->end_date,
            'combinable' => $affiliation->combinable,
        ];
    }
}
