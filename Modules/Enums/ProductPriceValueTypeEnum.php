<?php

namespace Modules\Enums;

enum ProductPriceValueTypeEnum: string
{
    case FIXED_VALUE = 'fixed_value';
    case PERCENTAGE = 'percentage';
}
