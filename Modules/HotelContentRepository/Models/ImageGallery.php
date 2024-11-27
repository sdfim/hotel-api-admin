<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\ImageGalleryFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ImageGallery extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory(): ImageGalleryFactory
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
        return $this->belongsToMany(Image::class, 'pd_gallery_images', 'gallery_id', 'image_id');
    }
    public function products()
    {
        return $this->belongsToMany(Hotel::class, 'pd_product_gallery', 'gallery_id', 'product_id');
    }

    public function scopeHasProduct($query, $hotelId)
    {
        return $query->whereHas('products', function ($q) use ($hotelId) {
            $q->where('product_id', $hotelId);
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

    public function productPromotions()
    {
        return $this->belongsToMany(ProductPromotion::class, 'pd_product_promotion_gallery', 'gallery_id', 'product_promotion_id');
    }

    public function scopeHasProductPromotion($query, $productPromotionId)
    {
        return $query->whereHas('productPromotions', function ($q) use ($productPromotionId) {
            $q->where('product_promotion_id', $productPromotionId);
        });
    }
}
