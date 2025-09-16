<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirwallexApiLog extends Model
{
    protected $fillable = [
        'method',
        'payment_intent_id',
        'direction',
        'payload',
        'response',
        'status_code',
        'booking_id',
    ];

    protected $casts = [
        'direction' => 'array',
        'payload' => 'array',
        'response' => 'array',
    ];
}
