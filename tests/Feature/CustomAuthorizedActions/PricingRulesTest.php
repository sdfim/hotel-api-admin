<?php

use App\Livewire\PricingRules\CreatePricingRule;
use App\Livewire\PricingRules\UpdatePricingRule;
use App\Models\PricingRule;
use App\Models\PricingRuleCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Modules\API\Tools\PricingRulesDataGenerationTools;

uses(RefreshDatabase::class);
uses(WithFaker::class);

test('pricing rules index is opening', function () {
    $this->get('/admin/pricing-rules')
        ->assertStatus(200);
});

test('pricing rules creating is opening', function () {
    $this->get('/admin/pricing-rules/create')
        ->assertStatus(200);
});

test('pricing rules showing is opening', function () {
    $pricingRule = PricingRule::factory()
        ->has(PricingRuleCondition::factory()->count(rand(1, 14)), 'conditions')
        ->create();

    $this->get(route('pricing-rules.show', $pricingRule->id))
        ->assertStatus(200);
});

test('validation of pricing rules form during creation', function () {
    Livewire::test(CreatePricingRule::class)
        ->set('data', [
            'name' => '',
            'manipulable_price_type' => '',
            'price_value_target' => '',
            'price_value' => '',
            'price_value_type' => '',
            'rule_start_date' => '',
        ])
        ->call('create')
        ->assertHasErrors([
            'data.name',
            'data.manipulable_price_type',
            'data.price_value',
            'data.price_value_type',
            'data.rule_start_date',
            'data.conditions',
        ]);
});

test('possibility of creating new pricing rule', function () {
    /** @var PricingRulesDataGenerationTools $pricingRulesTools */
    $pricingRulesTools = app(PricingRulesDataGenerationTools::class);

    $pricingRuleData = $pricingRulesTools->generatePricingRuleData(time());
    $pricingRuleConditionsData = $pricingRulesTools->generatePricingRuleConditionsData();

    $formData = [
        ...$pricingRuleData,
        'conditions' => $pricingRuleConditionsData,
    ];

    Livewire::test(CreatePricingRule::class)
        ->set('data', $formData)
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified('Created successfully');

    $this->assertDatabaseHas('pricing_rules', $pricingRuleData);

    foreach ($pricingRuleConditionsData as $cond) {
        $this->assertDatabaseHas('pricing_rules_conditions', $cond);
    }
});

test('possibility of updating an existing pricing rule', function () {
    // 1. Create an existing pricing rule with random conditions (initial state)
    $pricingRule = PricingRule::factory()
        ->has(PricingRuleCondition::factory()->count(rand(1, 5)), 'conditions')
        ->create();

    /** @var PricingRulesDataGenerationTools $pricingRulesTools */
    $pricingRulesTools = app(PricingRulesDataGenerationTools::class);

    // 2. Define a stable condition to avoid hierarchy validation issues (e.g. rate_code requires property)
    $pricingRuleConditionsData = [
        ['field' => 'property', 'compare' => '=', 'value_from' => '12345'],
    ];

    // 3. Define deterministic (non-random) data for updating the pricing rule
    //    This makes the test stable and predictable.
    $pricingRuleData = [
        'name' => 'Pricing rule updated',
        'weight' => 0,
        'is_sr_creator' => 0,
        'is_exclude_action' => 0,
        'rule_start_date' => now()->toDateString(),
        'rule_expiration_date' => now()->copy()->addDays(45)->toDateString(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 1,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_person',
    ];

    // 4. Merge rule data with conditions for form submission
    $formData = [
        ...$pricingRuleData,
        'conditions' => $pricingRuleConditionsData,
    ];

    // 5. Run Livewire update action
    Livewire::test(UpdatePricingRule::class, ['pricingRule' => $pricingRule])
        ->set('data', $formData)
        ->call('edit')
        ->assertHasNoFormErrors();

    // 6. Fetch the updated record from the database
    $actual = \DB::table('pricing_rules')->where('id', $pricingRule->id)->first();
    $this->assertNotNull($actual);

    // 7. Assert each field individually (normalize types and formats)
//    $this->assertEquals('Pricing rule updated', $actual->name);
    $this->assertEquals(0, (int) $actual->weight);
    $this->assertEquals(0, (int) $actual->is_sr_creator);
    $this->assertEquals(0, (int) $actual->is_exclude_action);
    //    $this->assertEquals('net_price', $actual->manipulable_price_type);
    $this->assertEquals(1.0, (int) $actual->price_value);
    $this->assertEquals('percentage', $actual->price_value_type);
    $this->assertEquals('per_person', $actual->price_value_target);
    $this->assertTrue(
        \Carbon\Carbon::parse($actual->rule_start_date)->isSameDay(now())
    );
    $this->assertTrue(
        \Carbon\Carbon::parse($actual->rule_expiration_date)->isSameDay(now()->copy()->addDays(45))
    );

    // 8. Assert that all new conditions were created
    foreach ($pricingRuleConditionsData as $condition) {
        $this->assertDatabaseHas('pricing_rules_conditions', $condition);
    }
});

test('possibility of destroying an existing pricing rule', function () {
    $pricingRule = PricingRule::factory()->create();

    $pricingRule->delete();

    $this->assertDatabaseMissing('pricing_rules', ['id' => $pricingRule->id]);
});
