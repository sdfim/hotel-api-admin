<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigInsuranceDocumentationTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigInsuranceDocumentationType extends Model
{
    use HasFactory;

    protected $table = 'insurance_config_documentation_types';

    protected static function newFactory()
    {
        return ConfigInsuranceDocumentationTypeFactory::new();
    }

    protected $fillable = [
        'name_type',
        'viewable',
    ];

    protected $casts = [
        'viewable' => 'array',
    ];
}
