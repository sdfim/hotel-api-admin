<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Model;

class ConfigAttribute extends Model
{
    protected $fillable = [
        'name',
        'default_value',
    ];
}
