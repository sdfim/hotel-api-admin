<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingRuleCondition extends Model
{
    use HasFactory;

    protected $table = 'pricing_rules_conditions';

    /**
     * @var string[]
     */
    protected $fillable = [
        'field',
        'compare',
        'value_from',
        'value_to',
        'pricing_rule_id'
    ];
}
