<?php

namespace Modules\Enums;

enum InsuranceDocVisibilityEnum: string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';

    public static function getOptions(): array
    {
        return [
            self::INTERNAL->value => 'Internal',
            self::EXTERNAL->value => 'External',
        ];
    }
}
