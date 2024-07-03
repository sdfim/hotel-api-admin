<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiataGeography extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $connection;

    protected $fillable = [
        'city_id',
        'city_name',
        'locale_id',
        'locale_name',
        'country_code',
        'country_name',
    ];
}
