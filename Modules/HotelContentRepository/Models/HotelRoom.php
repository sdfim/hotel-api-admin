<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\HotelRoomFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HotelRoom extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return HotelRoomFactory::new();
    }

    protected $table = 'pd_hotel_rooms';

    protected $fillable = [
        'hotel_id',
        'external_code',
        'supplier_codes',
        'name',
        'area',
        'bed_groups',
        'room_views',
        'description',
        'related_rooms',
    ];

    protected $casts = [
        'amenities' => 'array',
        'bed_groups' => 'array',
        'room_views' => 'array',
        'related_rooms' => 'array',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
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

    public function affiliations(): HasMany
    {
        return $this->hasMany(ProductAffiliation::class, 'room_id', 'id');
    }

    public function relatedRooms()
    {
        return $this->belongsToMany(HotelRoom::class, 'pd_hotel_related_room_pivot_table', 'room_id', 'related_room_id');
    }

    public function getFullNameAttribute()
    {
        $res = "{$this->name}";
        if ($this->external_code) {
            $res .= " - {$this->external_code}";
        }
        return $res;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['hotel_id', 'external_code', 'name', 'description'])
            ->logOnlyDirty()
            ->useLogName('hotel_room');
    }
}
