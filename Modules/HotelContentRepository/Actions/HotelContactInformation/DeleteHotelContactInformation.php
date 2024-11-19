<?php

namespace Modules\HotelContentRepository\Actions\HotelContactInformation;

use Modules\HotelContentRepository\Events\HotelContactInformation\HotelContactInformationDeleted;
use Modules\HotelContentRepository\Models\HotelContactInformation;

class DeleteHotelContactInformation
{
    public function handle(HotelContactInformation $hotelContactInformation)
    {
        $hotelContactInformation->delete();
        HotelContactInformationDeleted::dispatch($hotelContactInformation);
    }
}
