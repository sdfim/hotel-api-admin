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
                'destination' => '',
                'travel_date' => '',
                'days' => '',
                'nights' => '',
                'supplier_id' => '',
                'rate_code' => '',
                'room_type' => '',
                'total_guests' => '',
                'room_guests' => '',
                'meal_plan' => '',
                'rating' => '',
                'price_type_to_apply' => '',
                'price_value_type_to_apply' => '',
                'price_value_to_apply' => '',
            ])
            ->call('create')
            ->assertHasErrors([
                'data.name',
                'data.destination',
                'data.travel_date',
                'data.days',
                'data.nights',
                'data.supplier_id',
                'data.rate_code',
                'data.room_type',
                'data.total_guests',
                'data.room_guests',
                'data.meal_plan',
                'data.rating',
                'data.price_type_to_apply',
                'data.price_value_type_to_apply',
                'data.price_value_to_apply',
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
            'rule_start_date' => date('Y-m-d H:i:s'),
            'rule_expiration_date' => date('Y-m-d H:i:s')
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
    public function test_possibility_of_storing_new_pricing_rule(): void
    {
        $this->auth();

        $supplier = Supplier::factory()->create();
        $channel = Channel::factory()->create();

        $data = [
            'name' => $this->faker->name,
            'property' => $this->faker->word,
            'destination' => $this->faker->word,
            'travel_date' => now(),
            'days' => 7,
            'nights' => 5,
            'channel_id' => $channel->id,
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
            'price_value_fixed_type_to_apply' => $this->faker->word,
        ];

        $response = $this->post(route('pricing_rules.store'), $data);

        $response->assertStatus(302)
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

        $pricingRule = PricingRule::factory()->create();
        $supplier = Supplier::factory()->create();
        $channel = Channel::factory()->create();

        $newData = [
            'name' => 'Updated Pricing Rule Name',
            'property' => 'Updated Pricing Rule Property',
            'destination' => 'Updated Pricing Rule Destination',
            'travel_date' => now(),
            'days' => 7,
            'nights' => 5,
            'supplier_id' => $supplier->id,
            'channel_id' => $channel->id,
            'rate_code' => 'dret1',
            'room_type' => 'vip',
            'total_guests' => 2,
            'room_guests' => 2,
            'number_rooms' => 1,
            'meal_plan' => 'plan',
            'rating' => 'rating',
        ];

        $response = $this->put(route('pricing_rules.update', [$pricingRule->id]), $newData);
        $response->assertStatus(302);
        $response->assertRedirect(route('pricing_rules.index'));

        $this->assertDatabaseHas('pricing_rules', $newData);
    }

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_destroying_an_existing_pricing_rule(): void
    {
        $this->auth();

        $pricingRule = PricingRule::factory()->create();

        $response = $this->delete(route('pricing_rules.destroy', [$pricingRule->id]));
        $response->assertStatus(302);
        $response->assertRedirect(route('pricing_rules.index'));

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
