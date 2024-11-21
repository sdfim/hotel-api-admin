<?php

namespace Modules\Enums;

enum CommissionValueTypeEnum: string
{
    case AMOUNT = 'Amount';
    case PERCENTAGE = 'Percentage';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
