<?php

namespace Modules\HotelContentRepository\Actions\Hotel;

use Modules\HotelContentRepository\Events\Hotel\HotelDeleted;
use Modules\HotelContentRepository\Models\Hotel;

class DeleteHotel
{
    public function handle(Hotel $hotel)
    {
        $hotel->delete();
        HotelDeleted::dispatch($hotel);
    }
}
