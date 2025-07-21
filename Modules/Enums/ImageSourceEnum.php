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
            self::OWN->value => 'Internal',
            self::CRM->value => 'CRM',
            self::EXTERNAL->value => 'External',
        ];
    }

    public function label(): string
    {
        return match ($this) {
            self::OWN => 'Internal',
            self::CRM => 'CRM',
            self::EXTERNAL => 'External',
        };
    }
}
