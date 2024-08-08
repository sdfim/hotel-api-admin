<?php

namespace Tests\Feature\CustomAuthorizedActions;

use App\Livewire\PricingRules\CreatePricingRule;
use App\Livewire\PricingRules\UpdatePricingRule;
use App\Models\PricingRule;
use App\Models\PricingRuleCondition;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Modules\API\Tools\PricingRulesDataGenerationTools;
use PHPUnit\Framework\Attributes\Test;

class PricingRulesTest extends CustomAuthorizedActionsTestCase
{
    use WithFaker;

    #[Test]
    public function test_pricing_rules_index_is_opening(): void
    {
        $response = $this->get('/admin/pricing-rules');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_pricing_rules_creating_is_opening(): void
    {
        $response = $this->get('/admin/pricing-rules/create');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_pricing_rules_showing_is_opening(): void
    {
        $pricingRule = PricingRule::factory()
            ->has(PricingRuleCondition::factory()->count(rand(1, 14)), 'conditions')
            ->create();

        $response = $this->get(route('pricing-rules.show', $pricingRule->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function test_validation_of_pricing_rules_form_during_creation(): void
    {
        Livewire::test(CreatePricingRule::class)
            ->set('data', [
                'name' => '',
                'manipulable_price_type' => '',
                'price_value_target' => '',
                'price_value' => '',
                'price_value_type' => '',
                'rule_start_date' => '',
                'rule_expiration_date' => '',
            ])
            ->call('create')
            ->assertHasErrors([
                'data.name',
                'data.manipulable_price_type',
                'data.price_value',
                'data.price_value_type',
                'data.rule_expiration_date',
                'data.rule_start_date',
                'data.conditions',
            ]);
    }

    #[Test]
    public function test_possibility_of_creating_new_pricing_rule(): void
    {
        $pricingRulesTools = new PricingRulesDataGenerationTools();

        $pricingRuleData = $pricingRulesTools->generatePricingRuleData(time());

        $pricingRuleConditionsData = $pricingRulesTools->generatePricingRuleConditionsData();

        $formData = [
            ...$pricingRuleData,
            'conditions' => $pricingRuleConditionsData,
        ];

        Livewire::test(CreatePricingRule::class)
            ->set('data', $formData)
            ->assertFormSet($formData)
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Created successfully')
            ->assertRedirect(route('pricing-rules.index'));

        $this->assertDatabaseHas('pricing_rules', $pricingRuleData);

        foreach ($pricingRuleConditionsData as $cond) {
            $this->assertDatabaseHas('pricing_rules_conditions', $cond);
        }
    }

    #[Test]
    public function test_possibility_of_updating_an_existing_pricing_rule(): void
    {
        $pricingRule = PricingRule::factory()
            ->has(PricingRuleCondition::factory()->count(rand(1, 14)), 'conditions')
            ->create();

        $pricingRulesTools = new PricingRulesDataGenerationTools();

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
            ->assertHasNoFormErrors()
            ->assertNotified('Updated successfully')
            ->assertRedirect(route('pricing-rules.index'));

        $this->assertDatabaseHas('pricing_rules', $pricingRuleData);

        foreach ($pricingRuleConditionsData as $pricingRuleConditionData) {
            $this->assertDatabaseHas('pricing_rules_conditions', $pricingRuleConditionData);
        }
    }

    #[Test]
    public function test_possibility_of_destroying_an_existing_pricing_rule(): void
    {
        $pricingRule = PricingRule::factory()->create();

        $pricingRule->delete();

        $this->assertDatabaseMissing('pricing_rules', ['id' => $pricingRule->id]);
    }
}
