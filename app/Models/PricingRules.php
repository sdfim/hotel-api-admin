<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRules extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'property',
        'destination',
        'travel_date',
        'datetime',
        'days',
        'nights',
        'supplier_id',
        'rate_code',
        'room_type',
        'total_guests',
        'room_guests',
        'number_rooms',
        'meal_plan',
        'rating',
        'price_type_to_apply',
        'price_value_type_to_apply',
        'price_value_to_apply',
        'price_value_fixed_type_to_apply',
        'channel_id',
        'rule_start_date',
        'rule_expiration_date'
    ];

    public function suppliers(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function giataProperties(): BelongsTo
    {
        return $this->belongsTo(GiataProperty::class, 'property', 'code');
    }

    public function channels(): BelongsTo
    {
        return $this->belongsTo(Channels::class, 'channel_id');
    }
}
