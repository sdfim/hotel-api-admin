<?php

namespace Modules\Enums;

enum SupplierNameEnum: string
{
    static function getValues()
    {
        return [self::EXPEDIA->value, self::HBSI->value, self::ICE_PORTAL->value];
    }

    case EXPEDIA = 'Expedia';
    case HBSI = 'HBSI';
    case ICE_PORTAL = 'IcePortal';
}
