<?php

namespace Modules\Enums;

enum MealPlansEnum: string
{
    case ALL_INCLUSIVE = 'All Inclusive';
    case DINNER_INCLUDED = 'Dinner Included';
    case LUNCH_INCLUDED = 'Lunch Included';
    case BREAKFAST_INCLUDED = 'Breakfast Included';
    case NO_MEAL_PLAN = 'No Meal Plan';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
