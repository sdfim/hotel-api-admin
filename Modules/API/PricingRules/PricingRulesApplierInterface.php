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
        string|int $rateCode,
        string|int $srRoomId,
        bool $b2b = true
    ): array;
}
