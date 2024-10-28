<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ConfigAttributeFactory;

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
