<?php

namespace Database\Factories;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfigAttributeFactory extends Factory
{
    protected $model = ConfigAttribute::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'default_value' => $this->faker->word,
        ];
    }
}
