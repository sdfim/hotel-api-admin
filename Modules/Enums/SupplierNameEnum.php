<?php

namespace Modules\Enums;

enum SupplierNameEnum: string
{
    case EXPEDIA = 'Expedia';
    case HBSI = 'HBSI';
    case ICE_PORTAL = 'IcePortal';
    case HILTON = 'Hilton';

    public static function getValues(): array
    {
        return [self::EXPEDIA->value, self::HBSI->value, self::ICE_PORTAL->value, self::HILTON->value];
    }

    // The order is important. Expedia should be first.
    public static function getContentSupplierValues(): array
    {
        return [self::EXPEDIA->value, self::ICE_PORTAL->value, self::HILTON->value];
    }

    public static function options(): array
    {
        return [
            self::EXPEDIA->value => self::EXPEDIA->value,
            self::ICE_PORTAL->value => self::ICE_PORTAL->value,
            self::HBSI->value => self::HBSI->value,
            self::HILTON->value => self::HILTON->value,
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
