<?php

namespace App\Actions\ConfigConsortium;

use App\Models\Configurations\ConfigConsortium;

class CreateConfigConsortium
{
    public function create(array $data): ConfigConsortium
    {
        return ConfigConsortium::create($data);
    }
}
