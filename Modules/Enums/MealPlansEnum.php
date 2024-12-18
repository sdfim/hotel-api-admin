<?php

namespace Modules\Enums;

enum MealPlansEnum: string
{
    case ALL_INCLUSIVE = 'Breakfast Included';
    case DINNER_INCLUDED = 'Dinner Included';
    case LUNCH_INCLUDED = 'Lunch Included';
    case BREAKFAST_INCLUDED = 'All Inclusive';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
