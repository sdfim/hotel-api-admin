<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Vendor;

/**
 * Class InsuranceRateTier
 *
 * @property int $id
 * @property int $vendor_id
 * @property float $min_trip_cost
 * @property float $max_trip_cost
 * @property float $consumer_plan_cost
 * @property float $ujv_retention
 * @property float $net_to_trip_mate
 *
 * @property InsuranceProvider $provider
 */
class InsuranceRateTier extends Model
{
    use HasFactory;

    protected $table = 'insurance_rate_tiers';

    protected $fillable = [
        'vendor_id',
        'min_trip_cost',
        'max_trip_cost',
        'consumer_plan_cost',
        'ujv_retention',
        'net_to_trip_mate',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
