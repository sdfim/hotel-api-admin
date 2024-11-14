<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InsuranceRateTier
 *
 * @property int $id
 * @property int $insurance_provider_id
 * @property float $min_trip_cost
 * @property float $max_trip_cost
 * @property float $consumer_plan_cost
 * @property float $uiv_retention
 * @property float $net_to_trip_mate
 *
 * @property InsuranceProvider $provider
 */
class InsuranceRateTier extends Model
{
    use HasFactory;

    protected $table = 'insurance_rate_tiers';

    protected $fillable = [
        'insurance_provider_id',
        'min_trip_cost',
        'max_trip_cost',
        'consumer_plan_cost',
        'uiv_retention',
        'net_to_trip_mate',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }
}
