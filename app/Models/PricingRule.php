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
        'channel_id',
        'days_until_travel',
        'destination',
        'meal_plan',
        'name',
        'nights',
        'number_rooms',
        'price_type_to_apply',
        'price_value_fixed_type_to_apply',
        'price_value_to_apply',
        'price_value_type_to_apply',
        'property',
        'rate_code',
        'rating',
        'room_guests',
        'room_type',
        'rule_expiration_date',
        'rule_start_date',
        'supplier_id',
        'total_guests',
        'total_guests_comparison_sign',
        'travel_date_from',
        'travel_date_to',
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
