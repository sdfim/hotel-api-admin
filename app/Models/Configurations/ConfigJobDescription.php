<?php

namespace App\Models\Configurations;

use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Hotel;

class ConfigJobDescription extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function hotels()
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
