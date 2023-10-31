<?php

namespace Modules\API\PricingRules;

use App\Models\PricingRule;

interface PricingRulesApplierInterface
{
    public function apply(int $giataId, int $channelId, array $requestArray, array $roomsPricingArray, array $pricingRule, bool $b2b = true): array;
}
