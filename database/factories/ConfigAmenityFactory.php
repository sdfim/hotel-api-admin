<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigAmenity;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigAmenityFactory extends Factory
{
    protected $model = ConfigAmenity::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
