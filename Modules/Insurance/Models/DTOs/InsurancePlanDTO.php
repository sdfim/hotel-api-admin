<?php

namespace Modules\Insurance\Models\DTOs;

use Modules\Insurance\Models\InsurancePlan;

class InsurancePlanDTO
{
    public array $data;
    public string $message;

    public function __construct(InsurancePlan $insurancePlan, string $message)
    {
        $this->data = [
            'booking_item' => $insurancePlan->booking_item,
            'total_insurance_cost' => $insurancePlan->total_insurance_cost,
            'insurance_provider_fee' => $insurancePlan->insurance_provider_fee,
            'commission_ujv' => $insurancePlan->commission_ujv,
            'insurance_provider_id' => $insurancePlan->insurance_provider_id,
            'request' => $insurancePlan->request,
            'id' => $insurancePlan->id,
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
        $this->message = $message;
    }
}
