<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ContentSource;

class ContentSourceTransformer extends TransformerAbstract
{
    public function transform(ContentSource $contentSource)
    {
        return [
            'name' => $contentSource->name,
        ];
    }
}
