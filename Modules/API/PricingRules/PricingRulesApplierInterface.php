<?php

namespace Modules\API\PricingRules;

interface PricingRulesApplierInterface
{
    public function apply(int $giataId, int $channelId, string $requestObject, string $roomsPricingObject): array;
}
