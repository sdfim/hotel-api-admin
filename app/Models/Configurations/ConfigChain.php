<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigChainFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigChain extends Model
{
    use HasFactory;

    protected static function newFactory(): ConfigChainFactory
    {
        return ConfigChainFactory::new();
    }

    protected $fillable = ['name'];
}
