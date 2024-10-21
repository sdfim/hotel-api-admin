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
            'name' => $this->faker->randomElement(RestrictionTypeNames::LIST),
        ];
    }
}
