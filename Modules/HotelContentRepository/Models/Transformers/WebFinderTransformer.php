<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\HotelWebFinder;

class WebFinderTransformer extends TransformerAbstract
{
    public function transform(HotelWebFinder $webFinder)
    {
        return [
            'finder' => $webFinder->finder,
            'example' => $webFinder->example,
            'type' => $webFinder->type,
        ];
    }
}
