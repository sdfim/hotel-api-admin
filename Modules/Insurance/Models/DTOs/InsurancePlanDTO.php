<?php

namespace Modules\Insurance\Models\DTOs;

use Modules\Insurance\Models\InsurancePlan;

class InsurancePlanDTO
{
    public array $data;

    public function __construct(InsurancePlan $insurancePlan)
    {
        $this->data = [
            'id' => $insurancePlan->id,
            'booking_item' => $insurancePlan->booking_item,
            'total_insurance_cost' => $insurancePlan->total_insurance_cost,
            'insurance_vendor_fee' => $insurancePlan->insurance_vendor_fee,
            'commission_ujv' => $insurancePlan->commission_ujv,
            'vendor_id' => $insurancePlan->insurance_provider_id,
            'vendor' => $insurancePlan->provider->name,
            'request' => $insurancePlan->request,
            'applications' => $insurancePlan->applications->map(function ($application) {
                return [
                    'id' => $application->id,
                    'insurance_plan_id' => $application->insurance_plan_id,
                    'room_number' => $application->room_number,
                    'name' => $application->name,
                    'location' => $application->location,
                    'age' => $application->age,
                    'applied_at' => $application->applied_at,
                    'total_insurance_cost_pp' => $application->total_insurance_cost_pp,
                ];
            })->toArray(),
        ];
    }
}
