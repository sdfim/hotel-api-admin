<?php

namespace App\Models;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformationalService extends Model
{
    use HasFactory;

    protected $table = 'booking_item_informative_service';

    protected $fillable = [
        'booking_item',
        'service_id',
        'cost',
    ];

    public function bookingItem()
    {
        return $this->belongsTo(ApiBookingItem::class, 'booking_item');
    }

    public function service()
    {
        return $this->belongsTo(ConfigServiceType::class);
    }
}
