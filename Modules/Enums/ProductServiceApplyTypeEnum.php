<?php

namespace Modules\Enums;

enum ProductServiceApplyTypeEnum: string
{
    case PER_NIGHT = 'per_night';
    case PER_PERSON = 'per_person';
    case PER_SERVICE = 'per_service';
    case PER_NIGHT_PER_PERSON = 'per_night_per_person';
}
