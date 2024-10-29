<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigDescriptiveType;
use App\Models\Enums\DescriptiveLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigDescriptiveTypeFactory extends Factory
{
    protected $model = ConfigDescriptiveType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'location' => $this->faker->randomElement(DescriptiveLocation::cases()),
            'type' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
