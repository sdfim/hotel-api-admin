<?php

namespace Modules\Enums;

enum ProductApplyTypeEnum: string
{
    case PER_NIGHT = 'per_night';
    case PER_PERSON = 'per_person';
    case PER_ROOM = 'per_room';
    case PER_NIGHT_PER_PERSON = 'per_night_per_person';
    case SPECIFIC_NIGHT = 'specific_night';
    case COUNT_OF_NIGHTS = 'count_of_nights';

    public function label(): string
    {
        return match ($this) {
            self::PER_NIGHT => 'Per Night',
            self::PER_PERSON => 'Per Person',
            self::PER_ROOM => 'Per Room',
            self::PER_NIGHT_PER_PERSON => 'Per Night Per Person',
            self::SPECIFIC_NIGHT => 'Specific Night',
            self::COUNT_OF_NIGHTS => 'Count of Nights',
        };
    }

    public static function options(): array
    {
        return [
            self::PER_NIGHT->value => self::PER_NIGHT->label(),
            self::PER_PERSON->value => self::PER_PERSON->label(),
            self::PER_ROOM->value => self::PER_ROOM->label(),
            self::PER_NIGHT_PER_PERSON->value => self::PER_NIGHT_PER_PERSON->label(),
            self::SPECIFIC_NIGHT->value => self::SPECIFIC_NIGHT->label(),
            self::COUNT_OF_NIGHTS->value => self::COUNT_OF_NIGHTS->label(),
        ];
    }
}
