<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelWebFinderFactory;

class HotelWebFinder extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelWebFinderFactory::new();
    }

    protected $table = 'pd_hotel_web_finders';

    protected $fillable = [
        'hotel_id',
        'base_url',
        'finder',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function units()
    {
        return $this->hasMany(HotelWebFinderUnit::class, 'web_finder_id');
    }
}
