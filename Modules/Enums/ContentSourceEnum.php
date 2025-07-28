<?php

namespace Modules\Enums;

enum ContentSourceEnum: string
{
//    case EXPEDIA = 'Expedia';
    case ICE_PORTAL = 'IcePortal';
    case HBSI = 'IBS';
//    case HILTON = 'Hilton';
    case INTERNAL = 'Internal';

    public static function options(): array
    {
        return [
//            self::EXPEDIA->value => self::EXPEDIA->value,
            self::ICE_PORTAL->value => self::ICE_PORTAL->value,
            self::HBSI->value => self::HBSI->value,
        ];
    }
}
