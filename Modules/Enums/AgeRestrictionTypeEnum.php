<?php

namespace Modules\Enums;

enum AgeRestrictionTypeEnum: string
{
    case MAX_CHILD_AGE = 'Max Child Age';
    case MAX_INFANT_AGE = 'Max Infant Age';
    case ADULTS_ONLY = 'Adults Only';
    case ADULTS_ONLY_SECTIONS = 'Adults Only Sections';
    case MINIMUM_AGE = 'Minimum Age';
}
