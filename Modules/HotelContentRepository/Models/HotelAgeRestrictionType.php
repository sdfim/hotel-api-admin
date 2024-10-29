<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelAgeRestrictionTypeFactory;

class HotelAgeRestrictionType extends Model
{
    use HasFactory;

    protected $table = 'pd_hotel_age_restriction_types';

    protected $fillable = [
        'name',
        'description',
    ];

    protected static function newFactory()
    {
        return HotelAgeRestrictionTypeFactory::new();
    }
}
