<?php

namespace Modules\API\Suppliers\Enums;

enum MappingSuppliersEnum: string
{
    case Expedia = 'Expedia';
    case HBSI = 'HBSI';
    case IcePortal = 'IcePortal';
    case HILTON = 'Hilton';
    case HOTEL_TRADER = 'HotelTrader';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::Expedia->value => self::Expedia->value,
            self::IcePortal->value => self::IcePortal->value,
            self::HBSI->value => self::HBSI->value,
            self::HILTON->value => self::HILTON->value,
            self::HOTEL_TRADER->value => self::HOTEL_TRADER->value,
        ];
    }
}
