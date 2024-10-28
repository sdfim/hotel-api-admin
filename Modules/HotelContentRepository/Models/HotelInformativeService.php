<?php

namespace Modules\HotelContentRepository\Models;

use App\Models\Configurations\ConfigServiceType;
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
        'service_id',
    ];

    protected $hidden = [
        'pivot'
    ];

    public $timestamps = false;

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function service()
    {
        return $this->belongsTo(ConfigServiceType::class);
    }
}
