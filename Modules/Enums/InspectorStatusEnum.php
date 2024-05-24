<?php

namespace Modules\Enums;

enum InspectorStatusEnum: string
{
    case ERROR = 'error';
    case SUCCESS = 'success';
    case PENDING = 'pending';
}
