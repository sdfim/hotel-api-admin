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
                    'vendor_id' => 1,
                    'min_trip_cost' => 0,
                    'max_trip_cost' => 1000,
                    'consumer_plan_cost' => 43,
                    'ujv_retention' => 10.75,
                    'net_to_trip_mate' => 32.25,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'vendor_id' => 1,
                    'min_trip_cost' => 1001,
                    'max_trip_cost' => 2000,
                    'consumer_plan_cost' => 86,
                    'ujv_retention' => 21.5,
                    'net_to_trip_mate' => 64.5,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'vendor_id' => 1,
                    'min_trip_cost' => 2001,
                    'max_trip_cost' => 3000,
                    'consumer_plan_cost' => 129,
                    'ujv_retention' => 32.25,
                    'net_to_trip_mate' => 96.75,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'vendor_id' => 1,
                    'min_trip_cost' => 3001,
                    'max_trip_cost' => 4000,
                    'consumer_plan_cost' => 172,
                    'ujv_retention' => 43,
                    'net_to_trip_mate' => 129,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'vendor_id' => 1,
                    'min_trip_cost' => 4001,
                    'max_trip_cost' => 5000,
                    'consumer_plan_cost' => 215,
                    'ujv_retention' => 53.75,
                    'net_to_trip_mate' => 161.25,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'vendor_id' => 1,
                    'min_trip_cost' => 5001,
                    'max_trip_cost' => 6000,
                    'consumer_plan_cost' => 258,
                    'ujv_retention' => 64.5,
                    'net_to_trip_mate' => 193.5,
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
