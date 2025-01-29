<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\HotelContentRepository\Models\Vendor;

/**
 * Class InsuranceRestriction
 *
 * @property int $id
 * @property int $provider_id
 * @property int $restriction_type_id
 * @property string $compare
 * @property mixed $value
 * @property InsuranceProvider $provider
 * @property InsuranceRestrictionType $restrictionType
 */
class InsuranceRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'restriction_type_id',
        'compare',
        'value',
        'sale_type',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function restrictionType(): BelongsTo
    {
        return $this->belongsTo(InsuranceRestrictionType::class, 'restriction_type_id');
    }
}
