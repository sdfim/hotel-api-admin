<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigRoomBedType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigRoomBedTypeFactory extends Factory
{
    protected $model = ConfigRoomBedType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
