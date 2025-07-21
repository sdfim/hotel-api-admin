<?php

declare(strict_types=1);

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Model;

class RoomCrm extends Model
{
    protected $table = 'pd_hotel_room_crm';

    protected $fillable = [
        'room_id',
        'crm_room_id',
    ];
}
