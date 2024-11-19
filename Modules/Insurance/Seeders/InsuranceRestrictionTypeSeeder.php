<?php

namespace Modules\Insurance\Seeders;


use Illuminate\Database\Seeder;
use Modules\Insurance\Models\Enums\RestrictionTypeNames;
use Modules\Insurance\Models\InsuranceRestrictionType;

class InsuranceRestrictionTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RestrictionTypeNames::cases() as $case) {
            InsuranceRestrictionType::updateOrCreate(
                ['name' => strtolower($case->name)],
                ['label' => $case->value]
            );
        }
    }
}
