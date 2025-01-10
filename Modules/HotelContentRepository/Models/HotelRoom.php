<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelRoomFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class HotelRoom extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return HotelRoomFactory::new();
    }

    protected $table = 'pd_hotel_rooms';

    protected $fillable = [
        'hotel_id',
        'hbsi_data_mapped_name',
        'supplier_codes',
        'name',
        'area',
        'bed_groups',
        'description',
        'supplier_codes',
    ];

    protected $casts = [
        'amenities' => 'array',
        'bed_groups' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function galleries()
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_hotel_room_gallery', 'hotel_room_id', 'gallery_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(ConfigAttribute::class, 'pd_hotel_room_attributes', 'hotel_room_id', 'config_attribute_id');
    }
}
