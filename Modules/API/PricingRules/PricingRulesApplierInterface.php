<?php

namespace Modules\API\PricingRules;

interface PricingRulesApplierInterface
{
    public function apply(int $giataId, int $channelId, array $requestArray, array $roomsPricingArray, array $pricingRule, bool $b2b = true): array;
}
