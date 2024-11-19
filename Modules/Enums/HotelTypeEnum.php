<?php

namespace Modules\Enums;

enum HotelTypeEnum: string
{
    case DIRECT_CONNECTION = 'Direct connection';
    case MANUAL_CONTRACT = 'Manual contract';
    case COMMISSION_TRACKING = 'Commission tracking';
}
