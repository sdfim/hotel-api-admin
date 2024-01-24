<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\PricingRule;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\API\Tools\PricingRulesTools;

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
        $giataIds = [10000011, 10000044, 10000066, 10000171, 10000215, 10000273, 10000320, 10000353, 10000433, 10000560];

        $supplierId = Supplier::factory()->create()->id;

        $channelId = Channel::factory()->create()->id;

        $giataId = $giataIds[rand(0, 9)];

        $pricingRulesTools = new PricingRulesTools();

        $pricingRule = $pricingRulesTools->generatePricingRuleData('Test rule');

        $pricingRuleConditionsData = $pricingRulesTools->generatePricingRuleConditionsData(1,$supplierId, $channelId, $giataId);

        $pricingRule['conditions'] = $pricingRuleConditionsData;

        return $pricingRule;
    }
}
