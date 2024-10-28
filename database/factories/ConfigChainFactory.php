<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigChain;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigChainFactory extends Factory
{
    protected $model = ConfigChain::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
