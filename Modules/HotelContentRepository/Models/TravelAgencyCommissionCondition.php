<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class TravelAgencyCommissionCondition extends Model
{
    use Filterable;
    use HasFactory;

    protected $table = 'pd_travel_agency_commissions_conditions';

    protected $fillable = [
        'travel_agency_commissions_id',
        'field',
        'value',
    ];

    public function travelAgencyCommission()
    {
        return $this->belongsTo(TravelAgencyCommission::class, 'travel_agency_commissions_id');
    }
}
