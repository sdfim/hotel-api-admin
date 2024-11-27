<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class DescriptiveContentsSectionTransformer extends TransformerAbstract
{
    public function transform(ProductDescriptiveContentSection $section)
    {
        return [
            'start_date' => $section->start_date,
            'end_date' => $section->end_date,
            'content' => $section->content,
        ];
    }
}
