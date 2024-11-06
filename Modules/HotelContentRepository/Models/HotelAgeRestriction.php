<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelAgeRestrictionFactory;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class HotelAgeRestriction extends Model
{
    use Filterable;
    use HasFactory;

    protected static function newFactory()
    {
        return HotelAgeRestrictionFactory::new();
    }

    protected $table = 'pd_hotel_age_restrictions';

    protected $fillable = [
        'hotel_id',
        'restriction_type_id',
        'value',
        'active',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function restrictionType()
    {
        return $this->belongsTo(HotelAgeRestrictionType::class, 'restriction_type_id');
    }
}
