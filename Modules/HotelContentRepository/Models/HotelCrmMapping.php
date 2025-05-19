<?php

declare(strict_types=1);

namespace Modules\HotelContentRepository\Models;

use Illuminate\Database\Eloquent\Model;

class HotelCrmMapping extends Model
{
    protected $table = 'pd_hotel_crm_mapping';

    protected $fillable = [
        'giata_code',
        'crm_hotel_id',
    ];
}
