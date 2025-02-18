<?php

namespace Modules\Insurance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'benefits',
    ];

    protected $casts = [
        'benefits' => 'array',
    ];
}
