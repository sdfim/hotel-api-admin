<?php

namespace App\Models\Enums;

enum PaymentStatusEnum: string
{
    case INIT = 'init';
    case CONFIRMED = 'confirmed';
}

