<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class InsurancePlan
 *
 * @property int $id
 * @property string $booking_item
 * @property float $total_insurance_cost
 * @property float $commission_ujv
 * @property float $insurance_provider_fee
 * @property int $insurance_provider_id
 * @property string|null $request
 *
 * @property InsuranceProvider $provider
 * @property InsuranceRestriction[] $restrictions
 * @property InsuranceApplication[] $applications
 */
class InsurancePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_item',
        'total_insurance_cost',
        'commission_ujv',
        'insurance_provider_fee',
        'insurance_provider_id',
        'request'
    ];

    protected $casts = [
        'total_insurance_cost' => 'float',
        'commission_ujv' => 'float',
        'insurance_provider_fee' => 'float',
        'request' => 'array'
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }

    public function restrictions(): HasMany
    {
        return $this->hasMany(InsuranceRestriction::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(InsuranceApplication::class);
    }
}
