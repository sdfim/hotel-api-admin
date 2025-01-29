<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class InsuranceRestrictionType
 *
 * @property int $id
 * @property string $name
 * @property string $label
 * @property InsuranceRestriction[] $restrictions
 */
class InsuranceRestrictionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
    ];

    public function restrictions(): HasMany
    {
        return $this->hasMany(InsuranceRestriction::class);
    }
}
