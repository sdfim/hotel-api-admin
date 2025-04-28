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

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(
            ConfigAttribute::class,
            'config_attribute_category_pivot', // Pivot table name
            'config_attribute_category_id',   // Foreign key in the pivot table
            'config_attribute_id'             // Related foreign key in the pivot table
        );
    }
}
