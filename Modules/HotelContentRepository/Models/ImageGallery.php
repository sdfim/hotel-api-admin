<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\ImageGalleryFactory;

class ImageGallery extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ImageGalleryFactory::new();
    }

    protected $table = 'pd_image_galleries';

    protected $fillable = [
        'gallery_name',
        'description',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function images()
    {
        return $this->belongsToMany(HotelImage::class, 'pd_gallery_images', 'gallery_id', 'image_id');
    }
    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'pd_hotel_gallery', 'gallery_id', 'hotel_id');
    }

    public function scopeHasHotel($query, $hotelId)
    {
        return $query->whereHas('hotels', function ($q) use ($hotelId) {
            $q->where('hotel_id', $hotelId);
        });
    }

    public function hotelRooms()
    {
        return $this->belongsToMany(HotelRoom::class, 'pd_hotel_room_gallery', 'gallery_id', 'hotel_room_id');
    }

    public function scopeHasHotelRoom($query, $hotelRoomId)
    {
        return $query->whereHas('hotelRooms', function ($q) use ($hotelRoomId) {
            $q->where('hotel_room_id', $hotelRoomId);
        });
    }

    public function hotelPromotions()
    {
        return $this->belongsToMany(HotelPromotion::class, 'pd_hotel_promotion_gallery', 'gallery_id', 'hotel_promotion_id');
    }

    public function scopeHasHotelPromotion($query, $hotelPromotionId)
    {
        return $query->whereHas('hotelPromotions', function ($q) use ($hotelPromotionId) {
            $q->where('hotel_promotion_id', $hotelPromotionId);
        });
    }
}
