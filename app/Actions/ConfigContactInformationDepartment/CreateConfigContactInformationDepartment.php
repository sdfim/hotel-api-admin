<?php

namespace App\Actions\ConfigContactInformationDepartment;

use App\Models\Configurations\ConfigContactInformationDepartment;

class CreateConfigContactInformationDepartment
{
    public function create(array $input): ?ConfigContactInformationDepartment
    {
        return ConfigContactInformationDepartment::create($input);
    }
}
