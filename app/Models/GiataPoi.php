<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiataPoi extends Model
{
    use HasFactory;

    protected $fillable = [
        'poi_id',
        'name_primary',
        'type',
        'country_code',
        'lat',
        'lon',
        'places',
        'name_others',
    ];

    protected $casts = [
        'places' => 'json',
        'name_others' => 'json',
    ];
}
