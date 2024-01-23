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
        'price_type_to_apply',
        'price_value_fixed_type_to_apply',
        'price_value_to_apply',
        'price_value_type_to_apply',
        'rule_expiration_date',
        'rule_start_date'
    ];

    /**
     * @return HasMany
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(PricingRuleCondition::class, 'pricing_rule_id', 'id');
    }
}
