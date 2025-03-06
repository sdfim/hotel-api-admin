<?php

namespace Modules\Enums;

enum SupplierNameEnum: string
{
    case EXPEDIA = 'Expedia';
    case HBSI = 'HBSI';
    case ICE_PORTAL = 'IcePortal';

    static function getValues(): array
    {
        return [self::EXPEDIA->value, self::HBSI->value, self::ICE_PORTAL->value];
    }

    public static function options(): array
    {
        return [
            self::EXPEDIA->value => self::EXPEDIA->value,
            self::ICE_PORTAL->value => self::ICE_PORTAL->value,
            self::HBSI->value => self::HBSI->value,
        ];
    }

    public static function optionsDriver(): array
    {
        return [
            self::EXPEDIA->value => self::EXPEDIA->value,
            self::HBSI->value => self::HBSI->value,
        ];
    }

    public static function getValuesDriver(): array
    {
        return [self::EXPEDIA->value, self::HBSI->value];
    }
}
