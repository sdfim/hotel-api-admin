<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductAffiliationDetail;

class AffiliationDetailTransformer extends TransformerAbstract
{
    public function transform(ProductAffiliationDetail $detail)
    {
        return [
            'affiliation_id' => $detail->affiliation_id,
            'consortia' => $detail->consortia->name,
            'description' => $detail->description,
            'start_date' => $detail->start_date,
            'end_date' => $detail->end_date,
        ];
    }
}
