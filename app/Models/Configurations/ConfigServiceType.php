<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ConfigServiceTypeFactory;

class ConfigServiceType extends Model
{
    use HasFactory;

    protected static function newFactory(): ConfigServiceTypeFactory
    {
        return ConfigServiceTypeFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
        'cost',
    ];
}
