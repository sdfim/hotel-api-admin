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
        'name',
        'commission_value',
        'date_range_start',
        'date_range_end',
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

    public function conditions()
    {
        return $this->hasMany(TravelAgencyCommissionCondition::class, 'travel_agency_commissions_id');
    }
}
