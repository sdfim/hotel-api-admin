<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigContactInformationDepartmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigContactInformationDepartment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function newFactory(): ConfigContactInformationDepartmentFactory
    {
        return ConfigContactInformationDepartmentFactory::new();
    }

    protected $fillable = ['name'];
}
