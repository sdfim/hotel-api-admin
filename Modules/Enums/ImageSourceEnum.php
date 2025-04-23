<?php

namespace Modules\Enums;

enum ImageSourceEnum: string
{
    case OWN = 'own';
    case CRM = 'crm';
    case EXTERNAL = 'external';

    public static function getOptions(): array
    {
        return [
            self::OWN->value => 'Own',
            self::CRM->value => 'CRM',
            self::EXTERNAL->value => 'External',
        ];
    }
}
