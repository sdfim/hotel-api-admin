<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsurancePriceRange extends Model
{
    use HasFactory;

    protected $table = 'insurance_price_ranges';

    protected $fillable = [
        'min_price',
        'max_price',
        'insurance_rate',
    ];
}
