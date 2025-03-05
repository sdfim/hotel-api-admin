<?php

namespace Modules\API\PricingRules;

interface PricingRulesApplierInterface
{
    public function apply(
        int $giataId,
        array $roomsPricingArray,
        string $roomName,
        string|int $roomCode,
        string|int $roomType,
        bool $b2b = true
    ): array;
}
