<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\KeyMapping;

class KeyMappingDTO
{
    public $id;
    public $product_id;
    public $key_id;
    public $key_mapping_owner_id;

    public function __construct(KeyMapping $keyMapping)
    {
        $this->id = $keyMapping->id;
        $this->product_id = $keyMapping->product_id;
        $this->key_id = $keyMapping->key_id;
        $this->key_mapping_owner_id = $keyMapping->key_mapping_owner_id;
    }
}
