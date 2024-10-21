<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceRestriction extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_plan_id',
        'provider_id',
        'restriction_type_id',
        'compare',
        'value',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class, 'insurance_plan_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'provider_id');
    }

    public function restrictionType(): BelongsTo
    {
        return $this->belongsTo(InsuranceRestrictionType::class, 'restriction_type_id');
    }
}
