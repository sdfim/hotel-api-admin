<?php

namespace Modules\Insurance\Models\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Insurance\Models\InsuranceProvider;

class InsuranceProviderFactory extends Factory
{
    protected $model = InsuranceProvider::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'contact_info' => $this->faker->phoneNumber . ', ' . $this->faker->email . ', ' . $this->faker->address,
        ];
    }
}
