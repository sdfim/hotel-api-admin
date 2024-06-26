<?php

namespace Database\Factories;

use App\Models\PricingRuleCondition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\API\Tools\PricingRulesDataGenerationTools;

class PricingRuleConditionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pricingRulesTools = new PricingRulesDataGenerationTools();

        return $pricingRulesTools->generatePricingRuleConditionData();
    }
}
