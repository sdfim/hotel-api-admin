<?php

namespace Modules\Enums;

enum HotelSaleTypeEnum: string
{
    case DIRECT_CONNECTION = 'Direct connection';
    case MANUAL_CONTRACT = 'Manual contract';
    case COMMISSION_TRACKING = 'Commission tracking';
}
