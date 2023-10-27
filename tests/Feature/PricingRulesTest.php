<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\PricingRule;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;
use App\Livewire\PricingRules\CreatePricingRules;
use App\Livewire\PricingRules\UpdatePricingRules;
use App\Models\Channel;

class PricingRulesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_pricing_rules_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/pricing_rules');
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_pricing_rules_creating_is_opening(): void
    {
        $this->auth();
        $response = $this->get('/admin/pricing_rules/create');
        $response->assertStatus(200);
    }

    /**
     * @test
     * @return void
     */
    public function test_pricing_rules_showing_is_opening(): void
    {
        $this->auth();

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
        $this->auth();

        Livewire::test(CreatePricingRules::class)
            ->set('data', [
                'name' => '',
                'property' => '',
                'destination' => '',
                'travel_date' => '',
                'nights' => '',
                'channel_id' => '',
                'supplier_id' => '',
                'total_guests' => '',
                'number_rooms' => '',
                'rating' => '',
                'price_type_to_apply' => '',
                'price_value_type_to_apply' => '',
                'price_value_to_apply' => '',
                'price_value_fixed_type_to_apply' => '',
                'rule_start_date' => '',
                'rule_expiration_date' => '',
            ])
            ->call('create')
            ->assertHasErrors([
                'data.name',
                'data.property',
                'data.destination',
                'data.travel_date',
                'data.nights',
                'data.channel_id',
                'data.supplier_id',
                'data.total_guests',
                'data.number_rooms',
                'data.rating',
                'data.price_type_to_apply',
                'data.price_value_type_to_apply',
                'data.price_value_to_apply',
                'data.rule_start_date',
                'data.rule_expiration_date',
            ]);
    }

    /**
     * @test
     * @return void
     */
    public function test_pricing_rules_form_validation_and_possibility_of_creating_new_pricing_rule(): void
    {
        $this->auth();

        $supplier = Supplier::factory()->create();
        $channels = Channel::factory()->create();
        $today = now();

        $data = [
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
            'price_value_to_apply' => 2.5,
            'price_value_fixed_type_to_apply' => null,
            'channel_id' => $channels->id,
            'rule_start_date' => $today,
            'rule_expiration_date' => $today->copy()->addDays(rand(30, 60)),
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
        $this->auth();

        $pricingRules = PricingRule::factory()->create();
        $supplier = Supplier::factory()->create();
        $channel = Channel::factory()->create();

        Livewire::test(UpdatePricingRules::class, ['pricingRules' => $pricingRules])
            ->set('data.name', $this->faker->name)
            ->set('data.property', $this->faker->numberBetween(1, 10000))
            ->set('data.destination', 'Updated Pricing Rule Destination')
            ->set('data.travel_date',now())
            ->set('data.days', 7)
            ->set('data.nights', 6)
            ->set('data.supplier_id',$supplier->id)
            ->set('data.channel_id', $channel->id)
            ->set('data.rate_code', 'dret1')
            ->set('data.room_type', 'vip')
            ->set('data.total_guests', 2)
            ->set('data.room_guests', 2)
            ->set('data.number_rooms', 1)
            ->set('data.meal_plan', 'plan')
            ->set('data.rating', 'rating')
            ->set('rule_start_date', '12.12.2024')
            ->set('rule_expiration_date', '29.12.2024')
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
        $this->auth();
        $pricingRule = PricingRule::factory()->create();
        $pricingRule->delete();
        $this->assertDatabaseMissing('pricing_rules', ['id' => $pricingRule->id]);
    }

    /**
     * @return void
     */
    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
