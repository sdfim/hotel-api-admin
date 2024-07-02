<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Channels>
 */
class ChannelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $token = auth()->user()->createToken('name');

        return [
            'token_id' => $token->accessToken->id,
            'access_token' => $token->plainTextToken,
            'name' => $this->faker->name(),
            'description' => $this->faker->name(),
        ];
    }
}
