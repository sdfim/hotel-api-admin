<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\PricingRules\CreatePricingRules;
use App\Livewire\PricingRules\UpdatePricingRules;
use App\Models\Channel;
use App\Models\PricingRule;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;

class PricingRulesTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_pricing_rules_index_is_opening(): void
    {
        $response = $this->get('/admin/pricing_rules');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_pricing_rules_creating_is_opening(): void
    {
        $response = $this->get('/admin/pricing_rules/create');

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_pricing_rules_showing_is_opening(): void
    {
        $pricingRule = PricingRule::factory()->create();

        $response = $this->get(route('pricing_rules.show', $pricingRule->id));

        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_validation_of_pricing_rules_form_during_creation(): void
    {
        Livewire::test(CreatePricingRules::class)
            ->set('data', [
                'channel_id' => '',
                'days_until_travel' => '',
                'destination' => '',
                'name' => '',
                'nights' => '',
                'number_rooms' => '',
                'price_type_to_apply' => '',
                'price_value_fixed_type_to_apply' => '',
                'price_value_to_apply' => '',
                'price_value_type_to_apply' => '',
                'property' => '',
                'rating' => null,
                'rate_code' => '',
//            'room_guests' => 2,
                'room_type' => '',
                'rule_expiration_date' => '',
                'rule_start_date' => '',
                'supplier_id' => '',
                'total_guests' => '',
                'total_guests_comparison_sign' => '',
                'travel_date_from' => '',
                'travel_date_to' => '',
            ])
            ->call('create')
            ->assertHasErrors([
                'data.name',
                'data.price_type_to_apply',
                'data.price_value_to_apply',
                'data.price_value_type_to_apply',
                'data.rule_expiration_date',
                'data.rule_start_date',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_pricing_rules_form_validation_and_possibility_of_creating_new_pricing_rule(): void
    {
        $supplier = Supplier::factory()->create();

        $channel = Channel::factory()->create();

        $today = now();

        $priceValueTypeToApply = $this->faker->randomElement(['fixed_value', 'percentage']);

        $data = [
            'channel_id' => $channel->id,
            'days_until_travel' => rand(1, 30),
            'destination' => 'New York',
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
//            'room_guests' => 2,
            'room_type' => $this->faker->word,
            'rule_expiration_date' => $today->copy()->addDays(rand(30, 60))->toDateTimeString(),
            'rule_start_date' => $today->toDateTimeString(),
            'supplier_id' => $supplier->id,
            'total_guests' => rand(1, 12),
            'total_guests_comparison_sign' => $this->faker->randomElement(['=', '<', '>']),
            'travel_date_from' => $today->copy()->addDay()->toDateTimeString(),
            'travel_date_to' => $today->copy()->addDays(rand(3, 7))->toDateTimeString(),
        ];

        Livewire::test(CreatePricingRules::class)
            ->set('data', $data)
            ->call('create')
            ->assertRedirect(route('pricing_rules.index'));

        $this->assertDatabaseHas('pricing_rules', $data);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_an_existing_pricing_rule(): void
    {
        $pricingRules = PricingRule::factory()->create();

        $supplier = Supplier::factory()->create();

        $channel = Channel::factory()->create();

        $today = now();

        $priceValueTypeToApply = $this->faker->randomElement(['fixed_value', 'percentage']);

        Livewire::test(UpdatePricingRules::class, ['pricingRules' => $pricingRules])
            ->set('data.channel_id', $channel->id)
            ->set('data.days_until_travel', rand(1, 30))
            ->set('data.destination', 'New York')
            ->set('data.meal_plan', $this->faker->word)
            ->set('data.name', $this->faker->name)
            ->set('data.nights', rand(1, 13))
            ->set('data.number_rooms', rand(1, 3))
            ->set('data.price_type_to_apply', $this->faker->randomElement(['total_price', 'net_price', 'rate_price']))
            ->set('data.price_value_fixed_type_to_apply', $priceValueTypeToApply === 'fixed_value' ?
                $this->faker->randomElement(['per_guest', 'per_room', 'per_night']) : null)
            ->set('data.price_value_to_apply', rand(1, 100))
            ->set('data.price_value_type_to_apply', $priceValueTypeToApply)
            ->set('data.property', $this->faker->numberBetween(1, 100000))
            ->set('data.rate_code', $this->faker->word)
            ->set('data.rating', $this->faker->randomFloat(2, 1, 5.5))
//            ->set('data.room_guests', 2)
            ->set('data.room_type', $this->faker->word)
            ->set('data.supplier_id', $supplier->id)
            ->set('data.total_guests', rand(1, 12))
            ->set('data.total_guests_comparison_sign', $this->faker->randomElement(['=', '<', '>']))
            ->set('rule_expiration_date', $today->copy()->addDays(rand(30, 60))->toDateString())
            ->set('rule_start_date', $today->toDateString())
            ->set('data.travel_date_from', $today->copy()->addDay()->toDateString())
            ->set('data.travel_date_to', $today->copy()->addDays(rand(3, 7))->toDateString())
            ->call('edit')
            ->assertRedirect(route('pricing_rules.index'));

        $this->assertDatabaseHas('pricing_rules', [
            'id' => $pricingRules->id,
            'supplier_id' => $supplier->id,
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_destroying_an_existing_pricing_rule(): void
    {
        $pricingRule = PricingRule::factory()->create();

        $pricingRule->delete();

        $this->assertDatabaseMissing('pricing_rules', ['id' => $pricingRule->id]);
    }
}
