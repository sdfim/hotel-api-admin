<?php

namespace Modules\Enums;

enum HotelSaleTypeEnum: string
{
    case DIRECT_CONNECTION = 'Direct Connection';
    case MANUAL_CONTRACT = 'Manual Contract';
    case COMMISSION_TRACKING = 'Commission Tracking';
    case HYBRID_DIRECT_CONNECT_MANUAL_CONTRACT = 'Hybrid Direct Connect & Manual Contract';
}
