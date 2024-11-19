<?php

namespace Modules\HotelContentRepository\Actions\HotelAgeRestriction;

use Modules\HotelContentRepository\Events\HotelAgeRestriction\HotelAgeRestrictionDeleted;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;

class DeleteHotelAgeRestriction
{
    public function handle(HotelAgeRestriction $hotelAgeRestriction)
    {
        $hotelAgeRestriction->delete();
        HotelAgeRestrictionDeleted::dispatch($hotelAgeRestriction);
    }
}
