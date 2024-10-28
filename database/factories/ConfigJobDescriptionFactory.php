<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigJobDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigJobDescriptionFactory extends Factory
{
    protected $model = ConfigJobDescription::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
