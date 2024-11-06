<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelPromotionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class HotelPromotion extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return HotelPromotionFactory::new();
    }

    protected $table = 'pd_hotel_promotions';

    protected $fillable = [
        'hotel_id',
        'promotion_name',
        'description',
        'validity_start',
        'validity_end',
        'booking_start',
        'booking_end',
        'terms_conditions',
        'exclusions',
        'deposit_info',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public static function getFilterableFields()
    {
        return (new static)->fillable;
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function galleries()
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_hotel_promotion_gallery', 'hotel_promotion_id', 'gallery_id');
    }
}
