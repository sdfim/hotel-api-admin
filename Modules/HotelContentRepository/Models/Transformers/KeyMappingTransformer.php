<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\KeyMapping;

class KeyMappingTransformer extends TransformerAbstract
{
    public function transform(KeyMapping $mapping)
    {
        return [
            'key' => $mapping->key_id,
            'value' => $mapping->keyMappingOwner->name,
        ];
    }
}
