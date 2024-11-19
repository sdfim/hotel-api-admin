<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\HotelDescriptiveContentSection;

class DescriptiveContentsSectionTransformer extends TransformerAbstract
{
    public function transform(HotelDescriptiveContentSection $section)
    {
        return [
            'start_date' => $section->start_date,
            'end_date' => $section->end_date,
            'content' => $section->content,
        ];
    }
}
