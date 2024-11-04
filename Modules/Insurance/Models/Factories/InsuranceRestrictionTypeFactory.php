<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\Constants\RestrictionTypeNames;
use Modules\Insurance\Models\InsuranceRestrictionType;

class InsuranceRestrictionTypeFactory extends Factory
{
    protected $model = InsuranceRestrictionType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(array_keys(RestrictionTypeNames::OPTIONS)),
            'label' => $this->faker->randomElement(array_values(RestrictionTypeNames::OPTIONS)),
        ];
    }
}
