<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigContactInformationDepartment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigContactInformationDepartmentFactory extends Factory
{
    protected $model = ConfigContactInformationDepartment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
        ];
    }
}
