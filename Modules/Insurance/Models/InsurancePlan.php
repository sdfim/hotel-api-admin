<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Vendor;

/**
 * Class InsurancePlan
 *
 * @property int $id
 * @property string $booking_item
 * @property float $total_insurance_cost
 * @property float $commission_ujv
 * @property float $insurance_vendor_fee
 * @property int $vendor_id
 * @property int $insurance_type_id
 * @property string|null $request
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
        'insurance_vendor_fee',
        'vendor_id',
        'insurance_type_id',
        'request',
    ];

    protected $casts = [
        'total_insurance_cost' => 'float',
        'commission_ujv' => 'float',
        'insurance_vendor_fee' => 'float',
        'request' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function insuranceType(): BelongsTo
    {
        return $this->belongsTo(InsuranceType::class, 'insurance_type_id');
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
