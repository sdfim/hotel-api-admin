<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ConfigAmenityFactory;

class ConfigAmenity extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ConfigAmenityFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
    ];
}
