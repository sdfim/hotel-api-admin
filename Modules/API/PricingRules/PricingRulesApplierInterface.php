<?php

namespace Modules\API\PricingRules;

interface PricingRulesApplierInterface
{
    public function apply(int $giataId, int $channelId, array $requestObject, array $roomsPricingObject): array;
}
