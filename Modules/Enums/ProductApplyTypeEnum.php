<?php

namespace Modules\Enums;

enum ProductApplyTypeEnum: string
{
    case PER_NIGHT = 'per_night';
    case PER_PERSON = 'per_person';
    case PER_ROOM = 'per_room';
    case PER_NIGHT_PER_PERSON = 'per_night_per_person';
}
