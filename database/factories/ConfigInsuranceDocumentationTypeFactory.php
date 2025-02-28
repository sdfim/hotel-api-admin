<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigAttribute;
use App\Models\Configurations\ConfigInsuranceDocumentationType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Enums\InsuranceDocTypeEnum;

class configInsuranceDocumentationTypeFactory extends Factory
{
    protected $model = ConfigInsuranceDocumentationType::class;

    public function definition()
    {
        return [
            'name_type' => $this->faker->randomElement([
                'privacy_policy',
                'terms_and_condition',
                'travel_protection_plan_summary',
                'schedule_of_benefits_plan_costs',
                'claim_process',
                'emergency_travel_assistance_platinum',
                'emergency_travel_assistance_silver',
                'tripmate_claims',
                'general_information',
            ]),
            'viewable' => $this->faker->randomElement([
                'External',
                'Internal',
                'Internal,External',
            ]),
        ];
    }
}
