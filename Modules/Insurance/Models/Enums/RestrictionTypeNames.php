<?php

namespace Modules\Insurance\Models\Enums;

enum RestrictionTypeNames: string
{
    case AGE = 'Age';
    case CUSTOMER_LOCATION = 'Customer Location';
    case INSURANCE_RETURN_PERIOD_DAYS = 'Insurance Return Period Days';
    case TRIP_COST = 'Trip Cost';
    case TRIP_DURATION_DAYS = 'Trip Duration Days';
    case TRAVEL_LOCATION = 'Travel Location';
}
