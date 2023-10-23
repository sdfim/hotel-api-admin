<?php

namespace Tests\Feature\PricingRules;

use App\Livewire\PricingRules\UpdatePricingRules;
use App\Models\PricingRules;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class UpdatePricingRulesFormTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     * @return void
     */
    public function test_possibility_of_updating_an_existing_pricing_rule(): void
    {
        $this->auth();
        $pricing_rules = PricingRules::factory()->create();
        $supplier = Supplier::factory()->create();
        Livewire::test(UpdatePricingRules::class, ['pricingRules' => $pricing_rules])
            ->set('data.name', 'Updated Name')
            ->set('data.property', $this->faker->word)
            ->set('data.destination', $this->faker->word)
            ->set('data.travel_date', date('Y-m-d H:i:s'))
            ->set('data.days', 7)
            ->set('data.nights', 5)
            ->set('data.supplier_id', $supplier->id)
            ->set('data.rate_code', $this->faker->word)
            ->set('data.room_type', $this->faker->word)
            ->set('data.total_guests', 2)
            ->set('data.room_guests', 2)
            ->set('data.number_rooms', 1)
            ->set('data.meal_plan', $this->faker->word)
            ->set('data.rating', $this->faker->word)
            ->set('data.price_type_to_apply', $this->faker->word)
            ->set('data.price_value_type_to_apply', $this->faker->word)
            ->set('data.price_value_to_apply', 2.5)
            ->set('data.price_value_fixed_type_to_apply')
            ->call('edit')
            ->assertRedirect(route('pricing_rules.index'));
        $this->assertDatabaseHas('pricing_rules', [
            'id' => $pricing_rules->id,
            'name' => 'Updated Name',
        ]);
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
