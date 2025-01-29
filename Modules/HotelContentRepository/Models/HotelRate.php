<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\Factories\HotelRateFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HotelRate extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return HotelRateFactory::new();
    }

    protected $table = 'pd_hotel_rates';

    protected $fillable = [
        'hotel_id',
        'name',
        'code',
        'room_ids',
    ];

    protected $casts = [
        'room_ids' => 'array',
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

    public function dates(): HasMany
    {
        return $this->hasMany(HotelRateDates::class, 'rate_id');
    }

    public function productDescriptiveContentSections(): HasMany
    {
        return $this->hasMany(ProductDescriptiveContentSection::class, 'rate_id');
    }

    public function productFeesAndTaxes(): HasMany
    {
        return $this->hasMany(ProductFeeTax::class, 'rate_id');
    }

    public function productAffiliations(): HasMany
    {
        return $this->hasMany(ProductAffiliation::class, 'rate_id');
    }

    public function productDepositInformation(): HasMany
    {
        return $this->hasMany(ProductDepositInformation::class, 'rate_id');
    }

    public function productCancellationPolicies(): HasMany
    {
        return $this->hasMany(ProductCancellationPolicy::class, 'rate_id');
    }

    public function productPromotions(): HasMany
    {
        return $this->hasMany(ProductPromotion::class, 'rate_id');
    }

    protected static function booted()
    {
        static::deleting(function ($hotelRate) {
            $hotelRate->productDescriptiveContentSections()->delete();
            $hotelRate->productFeesAndTaxes()->delete();
            $hotelRate->productAffiliations()->delete();
            $hotelRate->productDepositInformation()->delete();
            $hotelRate->productCancellationPolicies()->delete();
            $hotelRate->productPromotions()->delete();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['hotel_id', 'name', 'name', 'code', 'room_ids'])
            ->logOnlyDirty()
            ->useLogName('hotel_rate');
    }
}
