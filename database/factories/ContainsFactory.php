<?php

namespace Database\Factories;
use App\Models\Contains;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contains>
 */
class ContainsFactory extends Factory
{
    /**
	 * The name of the factory's corresponding model.
	 *
	 * @var string
	 */
	protected $model = Contains::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Flight',
			'description' => 'Test description',
        ];
    }
}
