<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class AffiliationTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'details',
    ];

    public function transform(ProductAffiliation $affiliation)
    {
        return [
            'affiliation_name' => $affiliation->affiliation_name,
            'combinable' => $affiliation->combinable,
        ];
    }

    public function includeDetails(ProductAffiliation $affiliation)
    {
        return $this->collection($affiliation->details, new AffiliationDetailTransformer());
    }
}
