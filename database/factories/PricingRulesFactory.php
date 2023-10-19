<?php

namespace Database\Factories;

use App\Models\Channels;
use App\Models\PricingRules;
use App\Models\Suppliers;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingRules>
 */
class PricingRulesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PricingRules::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $supplier = Suppliers::factory()->create();
        $channels = Channels::factory()->create();
        return [
            'name' => $this->faker->name,
            'property' => $this->faker->word,
            'destination' => $this->faker->word,
            'travel_date' => date('Y-m-d H:i:s'),
            'days' => 7,
            'nights' => 5,
            'supplier_id' => $supplier->id,
            'rate_code' => $this->faker->word,
            'room_type' => $this->faker->word,
            'total_guests' => 2,
            'room_guests' => 2,
            'number_rooms' => 1,
            'meal_plan' => $this->faker->word,
            'rating' => $this->faker->word,
            'price_type_to_apply' => $this->faker->word,
            'price_value_type_to_apply' => $this->faker->word,
            'price_value_to_apply' =>  2.5,
            'price_value_fixed_type_to_apply' => null,
            'channel_id' => $channels->id,
            'rule_start_date' => date('Y-m-d H:i:s'),
            'rule_expiration_date' => date('Y-m-d H:i:s')
        ];
    }
}
