<?php

namespace Modules\API\Suppliers\Expedia\Enums;

use Modules\API\Suppliers\Enums\CancellationPolicyTypesEnum;

enum PolicyCode: string
{
    case General = 'General';

    public static function getObeCode(): CancellationPolicyTypesEnum
    {
        return CancellationPolicyTypesEnum::General;
    }
}
