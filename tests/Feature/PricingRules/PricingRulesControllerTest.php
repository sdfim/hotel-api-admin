<?php

namespace Tests\Feature\PricingRules;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\PricingRules;
use App\Models\Suppliers;
use App\Models\User;

class PricingRulesControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_pricing_rules_index_is_opening(): void
    {
        $this->auth();

        $response = $this->get('/admin/pricing_rules');
        $response->assertStatus(200);
    }

    public function test_possibility_of_creating_pricing_rule(): void
    {
        // $this->auth();
        // $response = $this->get(route('pricing_rules.create'));

        // $response->assertStatus(200)
        //     ->assertViewIs('dashboard.pricing-rules.create');
    }

    public function test_possibility_of_storing_new_pricing_rule(): void
    {
        $this->auth();

        $supplier = Suppliers::factory()->create();
        $data = [
            'name' => $this->faker->name,
            'property' => $this->faker->word,
            'destination' => $this->faker->word,
            'travel_date' => now(),
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
            'price_value_fixed_type_to_apply' => $this->faker->word,
        ];

        $response = $this->post(route('pricing_rules.store'), $data);

        $response->assertStatus(302)
            ->assertRedirect(route('pricing_rules.index'));
        $this->assertDatabaseHas('pricing_rules', $data);
    }

    public function test_possibility_of_showing_an_existing_pricing_rule(): void
    {
        // $this->auth();

        // $pricingRule = PricingRules::factory()->create();

        // $response = $this->get(route('pricing_rules.show', $pricingRule->id));

        // $response->assertStatus(200)
        //     ->assertViewIs('dashboard.pricing-rules.show')
        //     ->assertViewHas('pricingRule', $pricingRule);
    }

    public function test_possibility_of_editing__an_existing_pricing_rule(): void
    {
        // $this->auth();

        // $pricingRule = PricingRules::factory()->create();

        // $response = $this->get(route('pricing_rules.edit', $pricingRule->id));

        // $response->assertStatus(200)
        //     ->assertViewIs('dashboard.pricing-rules.update');
        // // ->assertViewHas('pricingRule', $pricingRule);
    }

    public function test_possibility_of_updating_an_existing_pricing_rule(): void
    {
        $this->auth();

        $pricingRule = PricingRules::factory()->create();
        $supplier = Suppliers::factory()->create();

        $newData = [
            'name' => 'Updated Pricing Rule Name',
            'property' => 'Updated Pricing Rule Property',
            'destination' => 'Updated Pricing Rule Destination',
            'travel_date' => now(),
            'days' => 7,
            'nights' => 5,
            'supplier_id' => $supplier->id,
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

    public function test_possibility_of_destroying_an_existing_pricing_rule(): void
    {
        $this->auth();

        $pricingRule = PricingRules::factory()->create();

        $response = $this->delete(route('pricing_rules.destroy', [$pricingRule->id]));
        $response->assertStatus(302);
        $response->assertRedirect(route('pricing_rules.index'));

        $this->assertDatabaseMissing('pricing_rules', ['id' => $pricingRule->id]);
    }

    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
