<?php

namespace Modules\API\Suppliers\Enums\Expedia;

use Modules\API\Suppliers\Enums\CancellationPolicyTypesEnum;

enum PolicyCode: string
{
    case General = 'General';

    public static function getObeCode(): CancellationPolicyTypesEnum
    {
        return CancellationPolicyTypesEnum::General;
    }
}
