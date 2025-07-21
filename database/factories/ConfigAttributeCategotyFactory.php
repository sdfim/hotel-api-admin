<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigAttributeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigAttributeCategotyFactory extends Factory
{
    protected $model = ConfigAttributeCategory::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
