<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigAmenity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAffiliationAmenity extends Model
{
    use HasFactory;

    protected $table = 'pd_product_affiliation_amenities';

    protected $fillable = [
        'product_affiliation_id',
        'amenity_id',
        'consortia',
        'is_paid',
        'price',
        'apply_type',
        'min_night_stay',
        'max_night_stay',
        'priority_rooms',
        'drivers',
    ];

    protected $casts = [
        'consortia' => 'array',
        'is_paid' => 'boolean',
        'price' => 'float',
        'priority_rooms' => 'array',
        'drivers' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function productAffiliation()
    {
        return $this->belongsTo(ProductAffiliation::class);
    }

    public function amenity()
    {
        return $this->belongsTo(ConfigAmenity::class);
    }

    public function priorityRooms()
    {
        return HotelRoom::whereIn('id', $this->priority_rooms);
    }
}
