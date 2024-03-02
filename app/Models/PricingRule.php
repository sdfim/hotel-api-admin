<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingRule extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'manipulable_price_type',
        'price_value_target',
        'price_value',
        'price_value_type',
        'rule_expiration_date',
        'rule_start_date'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rule_start_date' => 'datetime',
        'rule_expiration_date' => 'datetime',
    ];

    /**
     * @return HasMany
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(PricingRuleCondition::class, 'pricing_rule_id', 'id');
    }
}
