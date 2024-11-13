<?php

namespace App\Models\Configurations;

use App\Models\ApiBookingItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ConfigServiceTypeFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\HotelContentRepository\Models\HotelInformativeService;

class ConfigServiceType extends Model
{
    use HasFactory;

    protected static function newFactory(): ConfigServiceTypeFactory
    {
        return ConfigServiceTypeFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
        'cost',
    ];

    public function hotelInformativeServices(): HasMany
    {
        return $this->hasMany(HotelInformativeService::class);
    }

    public function bookingItems(): BelongsToMany
    {
        return $this->belongsToMany(ApiBookingItem::class, 'api_booking_item_service', 'service_id', 'booking_item');
    }
}
