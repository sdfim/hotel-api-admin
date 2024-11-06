<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelWebFinderUnitFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class HotelWebFinderUnit extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return HotelWebFinderUnitFactory::new();
    }

    protected $table = 'pd_hotel_web_finder_units';

    protected $fillable = [
        'web_finder_id',
        'field',
        'value',
    ];

    public static function getFilterableFields()
    {
        return (new static)->fillable;
    }

    public function webFinder()
    {
        return $this->belongsTo(HotelWebFinder::class, 'web_finder_id');
    }
}
