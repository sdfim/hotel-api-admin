<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelImageSectionFactory;

class HotelImageSection extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelImageSectionFactory::new();
    }

    protected $table = 'pd_hotel_image_sections';

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
