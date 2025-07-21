<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigConsortium;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigConsortiumFactory extends Factory
{
    protected $model = ConfigConsortium::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'description' => $this->faker->text(200),
        ];
    }
}
