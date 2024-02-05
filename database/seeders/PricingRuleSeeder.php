<?php

namespace Database\Seeders;

use App\Models\GiataProperty;
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
        $giataIds = GiataProperty::where('city_id', 961)->pluck('code')->all();

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
