<?php

namespace Tests\Feature;

use App\Livewire\PricingRules\CreatePricingRules;
use App\Models\Channels;
use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CreatePricingRulesFormTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testPricingRulesFormValidation()
    {
        $this->auth();

        Livewire::test(CreatePricingRules::class)
            ->set('data', [
                'name' => '',
                'destination' => '',

            ])
            ->call('create')
            ->assertHasErrors(['data.name', 'data.destination']);
    }
    public function testCreatePricingRulesFormAndValidation()
    {
        $this->auth();

        $supplier = Suppliers::factory()->create();
        $channels = Channels::factory()->create();
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
            'price_value_to_apply' =>  2.5,
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

    public function auth()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
