<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PricingRule;
use Illuminate\Database\Seeder;
use Modules\API\Tools\PricingRulesDataGenerationTools;

class PricingRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $giataIds = Property::where('city_id', 961)->limit(500)->pluck('code');

        $pricingRulesTools = new PricingRulesDataGenerationTools();

        foreach ($giataIds as $index => $giataId) {
            $ruleIndex = $index + 1;

            $pricingRuleData = $pricingRulesTools->generatePricingRuleData("#$ruleIndex");

            $pricingRule = PricingRule::create($pricingRuleData);

            $pricingRuleConditionsData = $pricingRulesTools->generatePricingRuleConditionsData($giataId);

            $pricingRule->conditions()->createMany($pricingRuleConditionsData);
        }
    }
}
