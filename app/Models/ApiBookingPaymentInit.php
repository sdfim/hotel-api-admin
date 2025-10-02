<?php

namespace App\Models;

use App\Models\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiBookingPaymentInit extends Model
{
    use HasFactory;

    protected $table = 'api_booking_payment_inits';

    protected $fillable = [
        'booking_id',
        'payment_intent_id',
        'action',
        'amount',
        'currency',
        'provider',
    ];

    protected $casts = [
        'amount' => 'float',
        'currency' => 'string',
        'action' => PaymentStatusEnum::class,
    ];
}
