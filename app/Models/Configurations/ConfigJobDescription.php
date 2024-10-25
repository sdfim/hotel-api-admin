<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Model;

class ConfigJobDescription extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
}
