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
    $pricingRule = PricingRule::factory()
        ->has(PricingRuleCondition::factory()->count(rand(1, 5)), 'conditions')
        ->create();

    /** @var PricingRulesDataGenerationTools $pricingRulesTools */
    $pricingRulesTools = app(PricingRulesDataGenerationTools::class);
    $pricingRuleData = $pricingRulesTools->generatePricingRuleData(time());
    $pricingRuleConditionsData = $pricingRulesTools->generatePricingRuleConditionsData($pricingRule->id);

    $formData = [
        ...$pricingRuleData,
        'conditions' => $pricingRuleConditionsData,
    ];

    Livewire::test(UpdatePricingRule::class, ['pricingRule' => $pricingRule])
        ->set('data', $formData)
        ->assertFormSet($formData)
        ->call('edit')
        ->assertHasNoFormErrors();

    $assertionData = $pricingRuleData;
    $assertionData['rule_start_date'] = $pricingRuleData['rule_start_date'].' 00:00:00';
    $assertionData['rule_expiration_date'] = $pricingRuleData['rule_expiration_date'].' 00:00:00';

    $this->assertDatabaseHas('pricing_rules', $assertionData);
});

test('possibility of destroying an existing pricing rule', function () {
    $pricingRule = PricingRule::factory()->create();

    $pricingRule->delete();

    $this->assertDatabaseMissing('pricing_rules', ['id' => $pricingRule->id]);
});
