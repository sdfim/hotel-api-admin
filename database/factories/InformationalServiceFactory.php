<?php

namespace Database\Factories;

use App\Models\InformationalService;
use Illuminate\Database\Eloquent\Factories\Factory;

class InformationalServiceFactory extends Factory
{
    protected $model = InformationalService::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'cost' => $this->faker->randomFloat(2, 10, 1000),
            'date' => $this->faker->date,
            'time' => $this->faker->time,
            'type' => $this->faker->randomElement(['type1', 'type2']),
        ];
    }
}
