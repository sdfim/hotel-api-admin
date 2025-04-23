<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelWebFinderFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HotelWebFinder extends Model
{
    use Filterable;
    use HasFactory;
    use LogsActivity;

    protected static function newFactory()
    {
        return HotelWebFinderFactory::new();
    }

    protected $table = 'pd_hotel_web_finders';

    protected $fillable = [
        'website',
        'base_url',
        'finder',
        'example',
    ];

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'pd_hotel_web_finder_hotel', 'web_finder_id', 'hotel_id');
    }

    public function units()
    {
        return $this->hasMany(HotelWebFinderUnit::class, 'web_finder_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['base_url', 'finder', 'type', 'example'])
            ->logOnlyDirty()
            ->useLogName('hotel_web_finder');
    }
}
