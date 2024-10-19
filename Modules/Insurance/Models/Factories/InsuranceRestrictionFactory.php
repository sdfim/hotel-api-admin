<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRestriction;
use Modules\Insurance\Models\InsuranceRestrictionType;

class InsuranceRestrictionFactory extends Factory
{
    protected $model = InsuranceRestriction::class;

    public function definition(): array
    {
        return [
            'insurance_plan_id' => InsurancePlan::factory(),
            'provider_id' => InsuranceProvider::factory(),
            'restriction_type_id' => InsuranceRestrictionType::factory(),
            'value' => $this->faker->word,
            'compare' => $this->faker->word,
        ];
    }
}
