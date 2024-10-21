<?php

namespace Modules\Insurance\Seeders;


use Illuminate\Database\Seeder;
use Modules\Insurance\Models\Constants\RestrictionTypeNames;
use Modules\Insurance\Models\InsuranceRestrictionType;

class InsuranceRestrictionTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RestrictionTypeNames::LIST as $type) {
            InsuranceRestrictionType::firstOrCreate(['name' => $type]);
        }
    }
}
