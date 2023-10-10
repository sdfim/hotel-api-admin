<?php

namespace Modules\API\PricingRules;

interface PricingRulesApplierInterface
{
    public function apply(array $expediaPricing): array;
}
