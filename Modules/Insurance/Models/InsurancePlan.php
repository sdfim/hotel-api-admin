<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsurancePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_item',
        'total_insurance_cost',
        'commission_ujv',
        'supplier_fee',
        'insurance_provider_id',
        'request'
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
