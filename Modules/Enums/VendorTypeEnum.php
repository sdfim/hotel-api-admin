<?php

namespace Modules\Enums;

enum VendorTypeEnum: string
{
    case HOTEL = 'hotel';
    case INSURANCE = 'insurance';
    case TOUR = 'tour';
    case TRANSFER = 'transfer';

    public static function getOptions(): array
    {
        return [
            self::HOTEL->value => ucfirst(self::HOTEL->value),
            self::INSURANCE->value => ucfirst(self::INSURANCE->value),
            self::TOUR->value => ucfirst(self::TOUR->value),
            self::TRANSFER->value => ucfirst(self::TRANSFER->value),
        ];
    }
}
