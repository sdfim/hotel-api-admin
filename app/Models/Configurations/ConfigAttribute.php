<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ConfigAttributeFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConfigAttribute extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ConfigAttributeFactory::new();
    }

    protected $fillable = [
        'name',
        'default_value',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ConfigAttributeCategory::class,
            'config_attribute_category_pivot', // Pivot table name
            'config_attribute_id',            // Foreign key in the pivot table
            'config_attribute_category_id'    // Related foreign key in the pivot table
        );
    }
}
