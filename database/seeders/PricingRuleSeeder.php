<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\GiataProperty;
use App\Models\PricingRule;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Modules\API\Tools\PricingRulesTools;

class PricingRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channelId = Channel::first()->id;

        $supplierId = Supplier::first()->id;

        $giataIds = GiataProperty::where('city_id', 961)->pluck('code')->all();

        $pricingRulesTools = new PricingRulesTools();

        foreach ($giataIds as $index => $giataId) {
            $pricingRuleData = $pricingRulesTools->generatePricingRuleData("#$index");

            $pricingRule = PricingRule::create($pricingRuleData);

            $pricingRuleConditionsData = $pricingRulesTools->generatePricingRuleConditionsData($pricingRule->id, $supplierId, $channelId, $giataId);

            $pricingRule->conditions()->createMany($pricingRuleConditionsData);
        }
    }
}
