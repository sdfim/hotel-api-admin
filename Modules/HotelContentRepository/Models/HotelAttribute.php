<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigAttribute;
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
        'attribute_id',
    ];

    protected $hidden = [
        'pivot'
    ];

    public $timestamps = false;

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function attribute()
    {
        return $this->belongsTo(ConfigAttribute::class);
    }
}
