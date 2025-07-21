<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class MappingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'giata_id' => $this->faker->numberBetween(1000, 9999),
            'supplier' => $this->faker->randomElement(MappingSuppliersEnum::values()),
            'supplier_id' => $this->faker->numberBetween(1000, 9999),
            'match_percentage' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
