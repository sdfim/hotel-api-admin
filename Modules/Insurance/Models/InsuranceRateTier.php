<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceRateTier extends Model
{
    use HasFactory;

    protected $table = 'insurance_rate_tiers';

    protected $fillable = [
        'insurance_provider_id',
        'min_price',
        'max_price',
        'rate_type',
        'rate_value',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }
}
