<?php

namespace Modules\HotelContentRepository\Actions\HotelAffiliation;

use Modules\HotelContentRepository\Events\HotelAffiliation\HotelAffiliationDeleted;
use Modules\HotelContentRepository\Models\HotelAffiliation;

class DeleteHotelAffiliation
{
    public function handle(HotelAffiliation $hotelAffiliation)
    {
        $hotelAffiliation->delete();
        HotelAffiliationDeleted::dispatch($hotelAffiliation);
    }
}
