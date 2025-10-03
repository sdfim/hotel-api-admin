<?php

namespace App\Models;

use App\Models\Enums\PaymentStatusEnum;
use App\Repositories\ApiBookingInspectorRepository;
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
        'related_id',
        'related_type',
    ];

    protected $casts = [
        'amount' => 'float',
        'currency' => 'string',
        'action' => PaymentStatusEnum::class,
    ];

    public function getBookingCostAttribute(): ?float
    {
        return ApiBookingInspectorRepository::getPriceBookingId($this->booking_id);
    }

    public function related()
    {
        return $this->morphTo();
    }
}
