<?php

namespace App\Actions\ConfigAttribute;

use App\Models\Configurations\ConfigAttribute;

class CreateConfigAttribute
{
    public function create(array $data): ConfigAttribute
    {
        return ConfigAttribute::create($data);
    }
}
