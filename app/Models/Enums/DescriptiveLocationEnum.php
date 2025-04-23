<?php

namespace App\Models\Enums;

enum DescriptiveLocationEnum: string
{
    case ALL = 'all';
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
}
