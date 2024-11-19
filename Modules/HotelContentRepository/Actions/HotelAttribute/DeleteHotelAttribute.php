<?php

namespace Modules\HotelContentRepository\Actions\HotelAttribute;

use Modules\HotelContentRepository\Events\HotelAttribute\HotelAttributeDeleted;
use Modules\HotelContentRepository\Models\HotelAttribute;

class DeleteHotelAttribute
{
    public function handle(HotelAttribute $hotelAttribute)
    {
        $hotelAttribute->delete();
        HotelAttributeDeleted::dispatch($hotelAttribute);
    }
}
