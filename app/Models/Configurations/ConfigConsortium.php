<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ConfigConsortiumFactory;

class ConfigConsortium extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ConfigConsortiumFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
    ];
}
