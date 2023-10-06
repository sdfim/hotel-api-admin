<?php

namespace Database\Factories;

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
    public function definition (): array
    {
        $supplier = Suppliers::factory()->create();
        return [
            'name' => $this->faker->name(),
            'property' => $this->faker->name(),
            'destination' => $this->faker->name(),
            'travel_date' => now(), // Поточна дата і час
            'days' => 7,
            'nights' => 5,
            'supplier_id' => $supplier->id, // Використовуємо ID створеного постачальника
            'rate_code' => $this->faker->name(),
            'room_type' => $this->faker->name(),
            'total_guests' => 2,
            'room_guests' => 2,
            'number_rooms' => 1,
            'meal_plan' => $this->faker->name(),
            'rating' => $this->faker->name(),
            'price_type_to_apply' => $this->faker->word,
            'price_value_type_to_apply' => $this->faker->word,
            'price_value_to_apply' => 2.47,
            'price_value_fixed_type_to_apply' => $this->faker->word,

        ];
    }
}
