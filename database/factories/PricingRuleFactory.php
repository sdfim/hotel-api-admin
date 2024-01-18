<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\PricingRule;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingRules>
 */
class PricingRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PricingRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $supplier = Supplier::factory()->create();

        $channel = Channel::factory()->create();

        $today = now();

        $priceValueTypeToApply = $this->faker->randomElement(['fixed_value', 'percentage']);

        return [
            'channel_id' => $channel->id,
            'days_until_travel' => rand(1, 30),
            'destination' => $this->faker->numberBetween(1, 100000),
            'meal_plan' => $this->faker->word,
            'name' => $this->faker->name,
            'nights' => rand(1, 13),
            'number_rooms' => rand(1, 3),
            'price_type_to_apply' => $this->faker->randomElement(['total_price', 'net_price', 'rate_price']),
            'price_value_fixed_type_to_apply' => $priceValueTypeToApply === 'fixed_value' ?
                $this->faker->randomElement(['per_guest', 'per_room', 'per_night']) : null,
            'price_value_to_apply' => rand(1, 100),
            'price_value_type_to_apply' => $priceValueTypeToApply,
            'property' => $this->faker->numberBetween(1, 100000),
            'rating' => $this->faker->randomFloat(2, 1, 5.5),
            'rate_code' => $this->faker->word,
            'room_guests' => 2,
            'room_type' => $this->faker->word,
            'rule_expiration_date' => $today->copy()->addDays(rand(30, 60))->toDateString(),
            'rule_start_date' => $today->toDateString(),
            'supplier_id' => $supplier->id,
            'total_guests' => rand(1, 12),
            'total_guests_comparison_sign' => $this->faker->randomElement(['=', '<', '>']),
            'travel_date_from' => $today->copy()->addDay()->toDateString(),
            'travel_date_to' => $today->copy()->addDays(rand(3, 7))->toDateString(),
        ];
    }
}
