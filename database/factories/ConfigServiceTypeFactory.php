<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigServiceTypeFactory extends Factory
{
    protected $model = ConfigServiceType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Room Service',
                'Laundry Service',
                'Concierge Service',
                'Shuttle Service',
                'Babysitting Service',
            ]),
            'description' => $this->faker->sentence(),
            'cost' => 0,
        ];
    }
}
