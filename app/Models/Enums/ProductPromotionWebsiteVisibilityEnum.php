<?php

namespace App\Models\Enums;

enum ProductPromotionWebsiteVisibilityEnum: string
{
    case NO_VISIBLE = 'no_visible';
    case VISIBLE_ALL = 'visible_all';
    case VISIBLE_UJV = 'visible_ujv';
    case VISIBLE_LUXURIA = 'visible_luxuria';

    public static function getOptions(): array
    {
        return [
            self::NO_VISIBLE->value => 'No Visible',
            self::VISIBLE_ALL->value => 'Visible All',
            self::VISIBLE_UJV->value => 'Visible UJV',
            self::VISIBLE_LUXURIA->value => 'Visible Luxuria',
        ];
    }
}
