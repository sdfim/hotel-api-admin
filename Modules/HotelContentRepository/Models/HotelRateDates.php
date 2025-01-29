<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Traits\Filterable;

class HotelRateDates extends Model
{
    use Filterable;
    use HasFactory;

    protected $table = 'pd_hotel_rate_dates';

    protected $fillable = [
        'rate_id',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function rate()
    {
        return $this->belongsTo(HotelRate::class);
    }
}
