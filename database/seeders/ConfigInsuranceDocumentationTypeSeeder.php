<?php

namespace Database\Seeders;

use App\Models\Configurations\ConfigInsuranceDocumentationType;
use Illuminate\Database\Seeder;

class ConfigInsuranceDocumentationTypeSeeder extends Seeder
{
    public function run()
    {
        $options = [
            'Privacy Policy' => ['External'],
            'Terms and Condition' => ['External'],
            'Travel Protection Plan Summary' => ['External'],
            'Schedule of Benefits Plan Costs' => ['Internal'],
            'Claim Process' => ['Internal'],
            'Emergency Travel Assistance Platinum' => ['External'],
            'Emergency Travel Assistance Silver' => ['External'],
            'Tripmate Claims' => ['Internal'],
            'General Information' => ['Internal', 'External'],
        ];

        foreach ($options as $nameType => $viewable) {
            ConfigInsuranceDocumentationType::firstOrCreate(
                ['name_type' => $nameType],
                ['viewable' => $viewable]
            );
        }
    }
}
