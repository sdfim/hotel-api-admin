<?php

namespace Modules\Insurance\Seeders;

use Illuminate\Database\Seeder;
use Modules\Insurance\Models\InsuranceProvider;

class InsuranceProviderSeeder extends Seeder
{
    public function run(): void
    {
        InsuranceProvider::firstOrCreate([
            'name' => 'TripMate',
        ], [
            'markup_type' => 'percentage',
            'markup_value' => 30,
            'contact_info' => '',
        ]);
    }
}
