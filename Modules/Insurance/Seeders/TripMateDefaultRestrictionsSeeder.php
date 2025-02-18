<?php

namespace Modules\Insurance\Seeders;

use Illuminate\Database\Seeder;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRestriction;
use Modules\Insurance\Models\InsuranceRestrictionType;
use Modules\Insurance\Models\InsuranceType;

class TripMateDefaultRestrictionsSeeder extends Seeder
{
    public function run(): void
    {
        $tripMateId = Vendor::where('name', 'TripMate')->pluck('id')->first();

        $silverPlan = InsuranceType::where('name', 'Silver Plan - F545U')->first();
        $platinumPlan = InsuranceType::where('name', 'Platinum Plan - F545F')->first();

        if ($tripMateId) {
            $restrictions = [
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'trip_duration_days')->pluck('id')->first(),
                    'insurance_type_id' => $silverPlan->id,
                    'compare' => '<',
                    'value' => 61,
                ],
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'trip_cost')->pluck('id')->first(),
                    'insurance_type_id' => $silverPlan->id,
                    'compare' => '<',
                    'value' => 30001,
                ],
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'insurance_return_period_days')->pluck('id')->first(),
                    'insurance_type_id' => $silverPlan->id,
                    'compare' => '=',
                    'value' => 14,
                ],
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'trip_duration_days')->pluck('id')->first(),
                    'insurance_type_id' => $platinumPlan->id,
                    'compare' => '<',
                    'value' => 61,
                ],
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'trip_cost')->pluck('id')->first(),
                    'insurance_type_id' => $platinumPlan->id,
                    'compare' => '<',
                    'value' => 20001,
                ],
                [
                    'vendor_id' => $tripMateId,
                    'restriction_type_id' => InsuranceRestrictionType::where('name', 'insurance_return_period_days')->pluck('id')->first(),
                    'insurance_type_id' => $platinumPlan->id,
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
