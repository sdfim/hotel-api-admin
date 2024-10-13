<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\TravelAgencyCommissionFactory;

class TravelAgencyCommission extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return TravelAgencyCommissionFactory::new();
    }

    protected $table = 'pd_travel_agency_commissions';

    protected $fillable = [
        'hotel_id',
        'consortium_id',
        'room_type',
        'commission_value',
        'date_range_start',
        'date_range_end',
        'created_at',
        'updated_at'
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
