<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\KeyMapping;

class KeyMappingDTO
{
    public $id;
    public $product_id;
    public $key_id;
    public $key_mapping_owner_id;

    public function __construct() {}

    public function transform(Collection $keyMappings)
    {
        return $keyMappings->map(function ($keyMapping) {
            return $this->transformKeyMapping($keyMapping);
        })->all();
    }

    public function transformKeyMapping(KeyMapping $keyMapping)
    {
        return [
            'id' => $keyMapping->id,
            'key_id' => $keyMapping->key_id,
            'key_mapping_owner_id' => $keyMapping->key_mapping_owner_id,
        ];
    }
}
