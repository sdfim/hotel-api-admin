<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceRestriction extends Model
{
    use HasFactory;

    protected $fillable = ['insurance_plan_id', 'restriction_type_id', 'location', 'min_age', 'max_age'];

    public function insurancePlan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class);
    }

    public function restrictionType(): BelongsTo
    {
        return $this->belongsTo(InsuranceRestrictionType::class);
    }
}
