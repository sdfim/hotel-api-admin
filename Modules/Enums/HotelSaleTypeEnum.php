<?php

namespace Modules\Enums;

enum HotelSaleTypeEnum: string
{
    case DIRECT_CONNECTION = 'Direct Connection';
    case MANUAL_CONTRACT = 'Manual Contract';
    case COMMISSION_TRACKING = 'Commission Tracking';
}
