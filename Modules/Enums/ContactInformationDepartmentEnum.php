<?php

namespace Modules\Enums;

enum ContactInformationDepartmentEnum: string
{
    case ACCOUNTING = 'Accounting';
    case RESERVATION = 'Reservation';
    case VIP_7_DAY = 'VIP 7 Day';
    case VIP_SU = 'VIP SU';
    case SALES_MARKETING = 'Sales Marketing';
    case CONCIERGE = 'Concierge';

    public static function values(): array
    {
        return array_map(fn ($enum) => $enum->value, self::cases());
    }

    public static function options(): array
    {
        return array_combine(self::values(), self::values());
    }
}
