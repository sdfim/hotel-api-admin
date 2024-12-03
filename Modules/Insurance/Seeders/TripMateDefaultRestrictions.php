<?php

namespace Modules\Insurance\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRestriction;
use Modules\Insurance\Models\InsuranceRestrictionType;

class TripMateDefaultRestrictions extends Seeder
{
    public function run(): void
    {
        $tripMateId = Vendor::where('name', 'TripMate')->pluck('id')->first();

        if ($tripMateId) {
            $restrictions = [
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'trip_duration_days')->pluck('id')->first(),
                    'compare' => '<',
                    'value' => 61,
                ],
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'trip_cost')->pluck('id')->first(),
                    'compare' => '<',
                    'value' => 30001,
                ],
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'insurance_return_period_days')->pluck('id')->first(),
                    'compare' => '=',
                    'value' => 14,
                ],
            ];

            foreach ($restrictions as $restriction) {
                InsuranceRestriction::firstOrCreate($restriction);
            }
        }
    }
}
