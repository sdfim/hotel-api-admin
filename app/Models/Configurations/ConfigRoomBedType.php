<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigRoomBedTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigRoomBedType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function newFactory(): ConfigRoomBedTypeFactory
    {
        return ConfigRoomBedTypeFactory::new();
    }

    protected $fillable = ['name'];
}
