<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\HotelContentRepository\Models\Factories\ProductPromotionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductPromotion extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return ProductPromotionFactory::new();
    }

    protected $table = 'pd_product_promotions';

    protected $fillable = [
        'product_id',
        'rate_id',
        'promotion_name',
        'rate_code',
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
        'not_refundable',
        'package',
    ];

    protected $casts = [
        'not_refundable' => 'boolean',
        'package' => 'boolean',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'promotion_name', 'description', 'validity_start', 'validity_end', 'booking_start', 'booking_end', 'terms_conditions', 'exclusions', 'deposit_info'])
            ->logOnlyDirty()
            ->useLogName('product_promotion');
    }
}
