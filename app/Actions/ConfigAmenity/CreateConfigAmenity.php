<?php

namespace App\Actions\ConfigAmenity;

use App\Models\Configurations\ConfigAmenity;

class CreateConfigAmenity
{
    public function create(array $data): ConfigAmenity
    {
        return ConfigAmenity::create($data);
    }
}
