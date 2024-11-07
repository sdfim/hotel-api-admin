<?php

namespace Modules\Insurance\Seeders;

use Illuminate\Database\Seeder;
use Modules\Insurance\Models\InsuranceRateTier;
use Carbon\Carbon;

class InsuranceRateTierSeeder extends Seeder
{
    public function run(): void
    {
        if (InsuranceRateTier::count() === 0) {
            $now = Carbon::now();

            $rateTiers = [
                [
                    'insurance_provider_id' => 1,
                    'min_price' => 150.00,
                    'max_price' => 30000.00,
                    'rate_type' => 'fixed',
                    'rate_value' => 150.00,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            foreach ($rateTiers as $rateTier) {
                InsuranceRateTier::create($rateTier);
            }
        }
    }
}
