<?php

namespace App\Models\Configurations;

use App\Models\Enums\DescriptiveLocationEnum;
use Database\Factories\ConfigDescriptiveTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigDescriptiveType extends Model
{
    use HasFactory;

    protected static function newFactory(): ConfigDescriptiveTypeFactory
    {
        return ConfigDescriptiveTypeFactory::new();
    }

    protected $fillable = [
        'name',
        'location',
        'type',
        'description',
    ];

    protected $casts = [
        'location' => DescriptiveLocationEnum::class,
    ];
}
