<?php

namespace Modules\Enums;

enum SupplierNameEnum: string
{
    case EXPEDIA = 'Expedia';
    case HBSI = 'HBSI';
    case ICE_PORTAL = 'IcePortal';
    case HILTON = 'Hilton';
    case GIATA = 'Giata';
    case HOTEL_TRADER = 'HotelTrader';

    private static function connectedSuppliers(): array
    {
        $suppliers = config('booking-suppliers.connected_suppliers', '');
        return array_filter(array_map('trim', explode(',', $suppliers)));
    }

    public static function getValues(): array
    {
        $all = [
            self::EXPEDIA->value,
            self::HBSI->value,
            self::ICE_PORTAL->value,
            self::HILTON->value,
            self::HOTEL_TRADER->value,
        ];
        return array_values(array_intersect($all, self::connectedSuppliers()));
    }

    public static function getContentSupplierValues(): array
    {
        $all = [
            self::EXPEDIA->value,
            self::ICE_PORTAL->value,
            self::HILTON->value,
            self::HOTEL_TRADER->value,
        ];
        return array_values(array_intersect($all, self::connectedSuppliers()));
    }

    public static function options(): array
    {
        $all = [
            self::EXPEDIA->value => self::EXPEDIA->value,
            self::ICE_PORTAL->value => self::ICE_PORTAL->value,
            self::HBSI->value => self::HBSI->value,
            self::HILTON->value => self::HILTON->value,
            self::HOTEL_TRADER->value => self::HOTEL_TRADER->value,
        ];
        $connected = self::connectedSuppliers();
        return array_intersect_key($all, array_flip($connected));
    }

    public static function contentOptions(): array
    {
        $all = [
            self::EXPEDIA->value => self::EXPEDIA->value,
            self::ICE_PORTAL->value => self::ICE_PORTAL->value,
            self::HILTON->value => self::HILTON->value,
            self::HOTEL_TRADER->value => self::HOTEL_TRADER->value,
        ];
        $connected = self::connectedSuppliers();
        return array_intersect_key($all, array_flip($connected));
    }

    public static function pricingList(): array
    {
        $all = [
            self::EXPEDIA->value,
            self::HBSI->value,
            self::HOTEL_TRADER->value,
        ];
        return array_values(array_intersect($all, self::connectedSuppliers()));
    }

    public static function optionsDriver(): array
    {
        $all = [
            self::EXPEDIA->value => self::EXPEDIA->value,
            self::HBSI->value => self::HBSI->value,
            self::HOTEL_TRADER->value => self::HOTEL_TRADER->value,
        ];
        $connected = self::connectedSuppliers();
        return array_intersect_key($all, array_flip($connected));
    }

    public static function getValuesDriver(): array
    {
        $all = [
            self::EXPEDIA->value,
            self::HBSI->value,
            self::HOTEL_TRADER->value,
        ];
        return array_values(array_intersect($all, self::connectedSuppliers()));
    }
}
