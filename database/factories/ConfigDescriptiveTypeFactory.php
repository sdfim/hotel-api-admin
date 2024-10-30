<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigDescriptiveTypeFactory extends Factory
{
    protected $model = ConfigDescriptiveType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Terms and Conditions',
                'Inclusions',
                'Cancellation Policy',
                'Privacy Policy',
                'User Agreement'
            ]),
            'location' => $this->faker->randomElement(['internal', 'external', 'all']),
            'type' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
