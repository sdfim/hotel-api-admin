<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Enums\InspectorStatusEnum;

class Reservation extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'booking_id',
        'booking_item',
        'date_offload',
        'date_travel',
        'passenger_surname',
        'reservation_contains',
        'channel_id',
        'total_cost',
        'paid',
        'canceled_at',
        'created_at',
        'updated_at',
    ];

    public function apiBookingItem(): HasOne
    {
        return $this->hasOne(ApiBookingItem::class, 'booking_item', 'booking_item');
    }

    public function apiBookingId(): HasOne
    {
        return $this->hasOne(ApiBookingInspector::class, 'booking_id', 'booking_id')
            ->where('type', 'book')
            ->where('sub_type', 'create')
            ->where('status', '!=', InspectorStatusEnum::ERROR->value);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function apiBookingsMetadata(): HasOne
    {
        return $this->hasOne(ApiBookingsMetadata::class, 'booking_item', 'booking_item');
    }
}
