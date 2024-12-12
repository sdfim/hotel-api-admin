<?php

namespace Modules\Enums;

enum MealPlansEnum: string
{
    case ALL_INCLUSIVE = 'Breakfast included';
    case DINNER_INCLUDED = 'Dinner included';
    case LUNCH_INCLUDED = 'Lunch included';
    case BREAKFAST_INCLUDED = 'All inclusive';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
