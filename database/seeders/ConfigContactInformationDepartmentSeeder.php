<?php

namespace Database\Seeders;

use App\Models\Configurations\ConfigContactInformationDepartment;
use Illuminate\Database\Seeder;
use Modules\Enums\ContactInformationDepartmentEnum;

class ConfigContactInformationDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ContactInformationDepartmentEnum::cases() as $department) {
            ConfigContactInformationDepartment::updateOrCreate(
                ['name' => $department->value],
                ['name' => $department->value]
            );
        }
    }
}
