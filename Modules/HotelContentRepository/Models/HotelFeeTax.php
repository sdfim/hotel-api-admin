<?php

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\HotelContentRepository\Models\Factories\HotelFeeTaxFactory;

class HotelFeeTax extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return HotelFeeTaxFactory::new();
    }

    protected $table = 'pd_hotel_fees_and_taxes';

    protected $fillable = [
        'name',
        'hotel_id',
        'net_value',
        'rack_value',
        'tax',
        'type',
        'fee_category',
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
