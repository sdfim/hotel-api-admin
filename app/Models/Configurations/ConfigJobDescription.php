<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigJobDescriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigJobDescription extends Model
{
    use HasFactory;

    protected static function newFactory(): ConfigJobDescriptionFactory
    {
        return ConfigJobDescriptionFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
    ];
}
