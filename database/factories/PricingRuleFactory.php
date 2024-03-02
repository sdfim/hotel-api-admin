<?php

namespace Database\Factories;

use App\Models\PricingRule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\API\Tools\PricingRulesDataGenerationTools;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingRules>
 */
class PricingRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PricingRule::class;

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
