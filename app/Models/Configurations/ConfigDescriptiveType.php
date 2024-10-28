<?php

namespace App\Models\Configurations;

use App\Models\Enums\DescriptiveLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigDescriptiveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'type',
        'description',
    ];

    protected $casts = [
        'location' => DescriptiveLocation::class,
    ];
}
