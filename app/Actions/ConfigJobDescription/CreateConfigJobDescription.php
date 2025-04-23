<?php

namespace App\Actions\ConfigJobDescription;

use App\Models\Configurations\ConfigJobDescription;

class CreateConfigJobDescription
{
    public function create(array $input): ?ConfigJobDescription
    {
        return ConfigJobDescription::create($input);
    }
}
