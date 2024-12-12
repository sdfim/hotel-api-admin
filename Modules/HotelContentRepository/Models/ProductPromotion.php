<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\HotelContentRepository\Models\Factories\ProductPromotionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class ProductPromotion extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return ProductPromotionFactory::new();
    }

    protected $table = 'pd_product_promotions';

    protected $fillable = [
        'product_id',
        'promotion_name',
        'description',
        'validity_start',
        'validity_end',
        'booking_start',
        'booking_end',
        'terms_conditions',
        'exclusions',
        'deposit_info',
        'min_night_stay',
        'max_night_stay',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function galleries(): BelongsToMany
    {
        return $this->belongsToMany(ImageGallery::class, 'pd_product_promotion_gallery', 'product_promotion_id', 'gallery_id');
    }
}
