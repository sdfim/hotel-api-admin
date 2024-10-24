<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Model;

class ConfigServiceType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'cost',
    ];
}
