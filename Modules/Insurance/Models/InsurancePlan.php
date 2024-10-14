<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsurancePlan extends Model
{
    use HasFactory;

    protected $fillable = ['booking_item', 'trip_cost_from', 'trip_cost_to', 'total_insurance_cost', 'commission', 'supplier_cost', 'min_trip_duration', 'max_trip_duration', 'valid_from', 'valid_to', 'insurance_provider_id'];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class);
    }

    public function restrictions(): HasMany
    {
        return $this->hasMany(InsuranceRestriction::class);
    }
}
