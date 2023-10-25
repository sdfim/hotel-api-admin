<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'property',
        'destination',
        'travel_date',
        'supplier_id',
        'channel_id',
        'days',
        'nights',
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
        'rule_start_date',
        'rule_expiration_date',
    ];

    /**
     * @return BelongsTo
     */
    public function suppliers(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * @return BelongsTo
     */
    public function giataProperties(): BelongsTo
    {
        return $this->belongsTo(GiataProperty::class, 'property', 'code');
    }

    /**
     * @return BelongsTo
     */
    public function channels(): BelongsTo
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }
}
