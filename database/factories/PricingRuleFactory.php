<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\API\Tools\PricingRulesDataGenerationTools;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingRules>
 */
class PricingRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pricingRulesTools = new PricingRulesDataGenerationTools();

        return $pricingRulesTools->generatePricingRuleData(time());
    }
}
