<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\Enums\RestrictionTypeNames;
use Modules\Insurance\Models\InsuranceRestrictionType;

class InsuranceRestrictionTypeFactory extends Factory
{
    protected $model = InsuranceRestrictionType::class;

    public function definition(): array
    {
        $case = $this->faker->randomElement(RestrictionTypeNames::cases());
        return [
            'name' => strtolower($case->name),
            'label' => $case->value,
        ];
    }
}
