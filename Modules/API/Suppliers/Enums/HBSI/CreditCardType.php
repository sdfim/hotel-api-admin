<?php

namespace Modules\API\Suppliers\Enums\HBSI;

enum CreditCardType: string
{
    case AX = 'AX';
    case BC = 'BC';
    case BL = 'BL';
    case CB = 'CB';
    case DN = 'DN';
    case DS = 'DS';
    case EC = 'EC';
    case JC = 'JC';
    case MC = 'MC';
    case TP = 'TP';
    case VI = 'VI';

    public static function getFrom(string $code): CreditCardType
    {
        return match ($code) {
            'MSC' => CreditCardType::MC,
            'VISA' => CreditCardType::VI,
            'AMEX' => CreditCardType::AX,
            'DIS' => CreditCardType::DS,
            default => CreditCardType::BC,

        };
    }
}
