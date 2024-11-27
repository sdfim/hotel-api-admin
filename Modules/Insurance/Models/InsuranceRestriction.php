<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InsuranceRestriction
 *
 * @property int $id
 * @property int $provider_id
 * @property int $restriction_type_id
 * @property string $compare
 * @property mixed $value
 *
 * @property InsuranceProvider $provider
 * @property InsuranceRestrictionType $restrictionType
 */
class InsuranceRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'restriction_type_id',
        'compare',
        'value',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'provider_id');
    }

    public function restrictionType(): BelongsTo
    {
        return $this->belongsTo(InsuranceRestrictionType::class, 'restriction_type_id');
    }
}
