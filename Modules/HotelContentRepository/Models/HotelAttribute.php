<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelAttributeFactory;

class HotelAttribute extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelAttributeFactory::new();
    }

    protected $table = 'pd_hotel_attributes';

    protected $fillable = [
        'hotel_id',
        'name',
        'attribute_value',
        'created_at'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
