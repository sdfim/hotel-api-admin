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
        'value',
        'value_from',
        'value_to',
        'pricing_rule_id',

    ];

    protected $casts = [
        'value' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->compare == 'in' || $model->compare == 'not_in') {
                $model->value_from = null;
            } else {
                $model->value = null;
            }
        });
    }
}
