<?php

namespace App\Models\Configurations;

use Database\Factories\ConfigJobDescriptionFactory;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\HotelContentRepository\Models\Hotel;

class ConfigJobDescription extends Model
{
    use HasFactory;

    protected static function newFactory(): ConfigJobDescriptionFactory
    {
        return ConfigJobDescriptionFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
    ];

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'pd_hotel_job_descriptions', 'job_description_id', 'hotel_id');
    }

    public function scopeHasHotel($query, $hotelId)
    {
        return $query->whereHas('hotels', function ($q) use ($hotelId) {
            $q->where('hotel_id', $hotelId);
        });
    }
}
