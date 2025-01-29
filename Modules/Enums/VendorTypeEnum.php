<?php

namespace Modules\Enums;

enum VendorTypeEnum: string
{
    case HOTEL = 'hotel';
    case INSURANCE = 'insurance';
    case TRIP = 'trip';
    case TRANSFER = 'transfer';

    public static function getOptions(): array
    {
        return [
            self::HOTEL->value => ucfirst(self::HOTEL->value),
            self::INSURANCE->value => ucfirst(self::INSURANCE->value),
            self::TRIP->value => ucfirst(self::TRIP->value),
            self::TRANSFER->value => ucfirst(self::TRANSFER->value),
        ];
    }
}
