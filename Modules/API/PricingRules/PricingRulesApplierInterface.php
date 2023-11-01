<?php

namespace Modules\API\PricingRules;

interface PricingRulesApplierInterface
{
    public function apply(int $giataId, array $roomsPricingArray, bool $b2b = true): array;
}
