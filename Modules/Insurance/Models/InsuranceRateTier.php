<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceRateTier extends Model
{
    use HasFactory;

    protected $table = 'insurance_rate_tiers';

    protected $fillable = [
        'min_price',
        'max_price',
        'insurance_rate',
    ];
}
