<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelInformativeServiceFactory;

class HotelInformativeService extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelInformativeServiceFactory::new();
    }

    protected $table = 'pd_hotel_informative_services';

    protected $fillable = [
        'hotel_id',
        'service_name',
        'service_description',
        'service_cost',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
