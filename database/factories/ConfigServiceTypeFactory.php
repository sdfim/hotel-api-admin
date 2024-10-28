<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigServiceTypeFactory extends Factory
{
    protected $model = ConfigServiceType::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'cost' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
