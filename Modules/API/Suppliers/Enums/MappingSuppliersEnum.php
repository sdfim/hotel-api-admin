<?php

namespace Modules\API\Suppliers\Enums;

enum MappingSuppliersEnum: string
{
    case Expedia = 'Expedia';
    case HBSI = 'HBSI';
    case IcePortal = 'IcePortal';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::EXPEDIA->value => self::EXPEDIA->value,
            self::ICEPORTAL->value => self::ICEPORTAL->value,
        ];
    }
}
