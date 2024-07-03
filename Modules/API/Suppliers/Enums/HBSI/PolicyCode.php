<?php

namespace Modules\API\Suppliers\Enums\HBSI;

use Modules\API\Suppliers\Enums\CancellationPolicyTypesEnum;

enum PolicyCode: string
{
    case CXP = 'General Cancellation Policy';
    case CKP = 'Early check-out penalty.';
    case CFC = 'Criteria for cancelling the reservation free of charge.';
    case CNS = 'Penalty for no show.';

    public static function fromCode(string $name): PolicyCode
    {
        if (defined("self::$name")) {
            return constant("self::$name");
        }

        return PolicyCode::CXP;
    }

    public static function getObeCode(PolicyCode $policyCode): CancellationPolicyTypesEnum
    {
        return match ($policyCode) {
            PolicyCode::CNS => CancellationPolicyTypesEnum::NoShow,
            default => CancellationPolicyTypesEnum::General,
        };
    }
}
