<?php

namespace Modules\Enums;

enum DaysPriorTypeEnum: string
{
    case DEPARTURE = 'Departure';
    case DATE = 'Date';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
