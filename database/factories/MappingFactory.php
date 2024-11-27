<?php

namespace Database\Factories;

use App\Models\Mapping;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class MappingFactory extends Factory
{
    protected $model = Mapping::class;

    public function definition()
    {
        return [
            'giata_id' => $this->faker->numberBetween(1000, 9999),
            'supplier' => $this->faker->randomElement(MappingSuppliersEnum::values()),
            'supplier_id' => $this->faker->numberBetween(1000, 9999),
            'match_percentage' => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
