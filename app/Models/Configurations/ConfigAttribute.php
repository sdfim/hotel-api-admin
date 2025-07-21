<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigAttributeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\Product;

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

    public function hotelRooms(): BelongsToMany
    {
        return $this->belongsToMany(
            HotelRoom::class,
            'pd_hotel_room_attributes',
            'config_attribute_id',
            'hotel_room_id'
        );
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'pd_product_attributes',
            'config_attribute_id',
            'product_id'
        );
    }

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
