<?php

namespace App\Models\Enums;

enum DescriptiveLocation: string
{
    case ALL = 'all';
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';
}
