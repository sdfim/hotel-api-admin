<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigAttributeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigAttribute extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ConfigAttributeFactory::new();
    }

    protected $fillable = [
        'name',
        'default_value',
    ];
}
