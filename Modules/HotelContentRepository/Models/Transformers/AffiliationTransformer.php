<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\HotelAffiliation;

class AffiliationTransformer extends TransformerAbstract
{
    public function transform(HotelAffiliation $affiliation)
    {
        return [
            'affiliation_name' => $affiliation->affiliation_name,
            'combinable' => $affiliation->combinable,
        ];
    }
}
