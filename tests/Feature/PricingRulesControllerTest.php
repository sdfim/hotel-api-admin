<?php

namespace Tests\Feature;

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

    public function testIndexPricingRule(): void
    {
        $this->auth();

        $response = $this->get('/pricing_rules');
        $response->assertStatus(200);
    }

    public function testCreatePricingRule()
    {
        $this->auth();

        $supplier = Suppliers::factory()->create();
        $data = [
            'name' => $this->faker->name,
            'property' => $this->faker->word,
            'destination' => $this->faker->word,
            'travel_date' => now(), // Поточна дата і час
            'days' => 7,
            'nights' => 5,
            'supplier_id' => $supplier->id, // Використовуємо ID створеного постачальника
            'rate_code' => $this->faker->word,
            'room_type' => $this->faker->word,
            'total_guests' => 2,
            'room_guests' => 2,
            'number_rooms' => 1,
            'meal_plan' => $this->faker->word,
            'rating' => $this->faker->word,
        ];

        $response = $this->post(route('pricing_rules.store'), $data);
        $response->assertStatus(302);
        $response->assertRedirect(route('pricing_rules.index'));

        $this->assertDatabaseHas('pricing_rules', $data);
    }

    public function testStorePricingRule()
    {
        $this->auth();

        $supplier = Suppliers::factory()->create();
        $data = [
            'name' => $this->faker->name,
            'property' => $this->faker->word,
            'destination' => $this->faker->word,
            'travel_date' => now(), // Поточна дата і час
            'days' => 7,
            'nights' => 5,
            'supplier_id' => $supplier->id, // Використовуємо ID створеного постачальника
            'rate_code' => $this->faker->word,
            'room_type' => $this->faker->word,
            'total_guests' => 2,
            'room_guests' => 2,
            'number_rooms' => 1,
            'meal_plan' => $this->faker->word,
            'rating' => $this->faker->word,
        ];

        $response = $this->post(route('pricing_rules.store'), $data);
        $response->assertRedirect(route('pricing_rules.index'));

        $this->assertDatabaseHas('pricing_rules', $data);

        $response->assertSessionHas('success', 'Pricing rule created successfully.');
    }

    public function testShow()
    {
        $this->auth();

        $pricingRule = PricingRules::factory()->create();

        $response = $this->get(route('pricing_rules.show', $pricingRule->id));
        $response->assertStatus(200);
    }

    public function testEditPricingRule()
    {
        $this->auth();
        $pricingRule = PricingRules::factory()->create();

        $response = $this->get(route('pricing_rules.edit', $pricingRule->id));
        $response->assertStatus(200);
    }

    public function testUpdatePricingRule()
    {
        $this->auth();

        $pricingRule = PricingRules::factory()->create();
        $supplier = Suppliers::factory()->create();

        $newData = [
            'name' => 'Updated Pricing Rule Name',
            'property' => 'Updated Pricing Rule Property',
            'destination' => 'Updated Pricing Rule Destination',
            'travel_date' => now(), // Поточна дата і час
            'days' => 7,
            'nights' => 5,
            'supplier_id' => $supplier->id, // Використовуємо ID створеного постачальника
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

    public function testDestroyPricingRule()
    {
        $this->auth();

        $pricingRule = PricingRules::factory()->create();

        $response = $this->delete(route('pricing_rules.destroy', [$pricingRule->id]));
        $response->assertStatus(302);
        $response->assertRedirect(route('pricing_rules.index'));
        
        $this->assertDatabaseMissing('pricing_rules', ['id' => $pricingRule->id]);
    }

    public function auth()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
