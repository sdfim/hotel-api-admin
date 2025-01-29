<?php

namespace Modules\Enums;

enum InsuranceRestrictionSaleTypeEnum: string
{
    case COMMISSION_TRACKING = 'Commission Tracking';
    case DIRECT_NET = 'Direct (Net)';

    public static function getOptions(): array
    {
        return [
            self::COMMISSION_TRACKING->value => self::COMMISSION_TRACKING->value,
            self::DIRECT_NET->value => self::DIRECT_NET->value,
        ];
    }
}
