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
        'price_type_to_apply',
        'price_value_fixed_type_to_apply',
        'price_value_to_apply',
        'price_value_type_to_apply',
        'rules',
        'rule_expiration_date',
        'rule_start_date'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'rules' => 'array'
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
