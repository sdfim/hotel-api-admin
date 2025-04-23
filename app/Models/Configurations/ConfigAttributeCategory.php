<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigAttributeCategotyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigAttributeCategory extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ConfigAttributeCategotyFactory::new();
    }

    protected $fillable = [
        'name',
    ];
}
