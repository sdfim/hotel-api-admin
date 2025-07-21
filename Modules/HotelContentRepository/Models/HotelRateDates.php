<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(HotelRate::class);
    }
}
