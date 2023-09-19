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

    public function testStore()
    {
        $this->auth();
        // Створюємо фейкові дані для тестування
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

        // Виконуємо POST-запит на маршрут створення PricingRule
        $response = $this->post(route('pricing-rules.store'), $data);

        // Перевіряємо, чи відбулася успішна переадресація
        $response->assertRedirect(route('pricing-rules.index'));

        // Перевіряємо, чи запис був створений у базі даних
        $this->assertDatabaseHas('pricing_rules', $data);

        // Перевіряємо повідомлення про успіх
        $response->assertSessionHas('success', 'Pricing rule created successfully.');
    }

    public function testCreatePricingRule()
    {
        $this->auth();
        $data = [
            'name' => 'Test Pricing Rule',
            // Додайте інші поля сюди
        ];

        $response = $this->post(route('pricing-rules.store'), $data);

        $response->assertRedirect(route('pricing-rules.index'));

        $this->assertDatabaseHas('pricing_rules', $data);
    }

    public function testShowPricingRule()
    {
        $this->auth();
        $pricingRule = PricingRules::factory()->create();

        $response = $this->get(route('pricing-rules.show', $pricingRule->id));

        $response->assertStatus(200);
    }

    public function testUpdatePricingRule()
    {
        $this->auth();
        $pricingRule = PricingRules::factory()->create();

        $newData = [
            'name' => 'Updated Pricing Rule Name',
            // Додайте інші поля сюди
        ];

        $response = $this->post(route('pricing-rules.update', $pricingRule->id), $newData);

        $response->assertRedirect(route('pricing-rules.index'));

        $this->assertDatabaseHas('pricing_rules', $newData);
    }

    public function testDeletePricingRule()
    {
        $this->auth();
        $pricingRule = PricingRules::factory()->create();

        $response = $this->post(route('pricing-rules.destroy', $pricingRule->id));

        $response->assertRedirect(route('pricing-rules.index'));

        $response->assertDeleted('pricing_rules', $pricingRule->toArray());
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
