<?php

namespace Modules\Enums;

enum RouteEnum: string
{

    case ROUTE_SEARCH = 'search';
    /**
     * @deprecated Use ROUTE_DETAIL_V1 instead.
     */
    case ROUTE_DETAIL = 'detail';
    case ROUTE_PRICE = 'price';

    case ROUTE_SEARCH_V1 = 'v1.search';
    case ROUTE_DETAIL_V1 = 'v1.detail';
    case ROUTE_PRICE_V1 = 'v1.price';
}
