<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelDepositInformationFactory;

class HotelDepositInformation extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelDepositInformationFactory::new();
    }

    protected $table = 'pd_hotel_deposit_information';

    protected $fillable = [
        'hotel_id',
        'days_departure',
        'pricing_parameters',
        'pricing_value',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
