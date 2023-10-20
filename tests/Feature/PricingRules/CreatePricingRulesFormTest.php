<?php

namespace Tests\Feature\PricingRules;

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

    public function test_pricing_rules_form_validation_and_possibility_of_creating_new_pricing_rule(): void
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

    public function auth(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
    }
}
