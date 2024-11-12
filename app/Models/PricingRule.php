<?php

namespace App\Models;

use Carbon\Carbon;
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
        'rule_start_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rule_start_date' => 'datetime',
            'rule_expiration_date' => 'datetime',
        ];
    }

    public function save(array $options = [])
    {
        if (empty($this->rule_expiration_date)) {
            $this->rule_expiration_date = Carbon::create(2100, 1, 1);
        }

        parent::save($options);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(PricingRuleCondition::class, 'pricing_rule_id', 'id');
    }
}
