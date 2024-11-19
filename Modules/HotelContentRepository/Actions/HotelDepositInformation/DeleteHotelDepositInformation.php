<?php

namespace Modules\HotelContentRepository\Actions\HotelDepositInformation;

use Modules\HotelContentRepository\Events\HotelDepositInformation\HotelDepositInformationDeleted;
use Modules\HotelContentRepository\Models\HotelDepositInformation;

class DeleteHotelDepositInformation
{
    public function handle(HotelDepositInformation $hotelDepositInformation)
    {
        $hotelDepositInformation->delete();
        HotelDepositInformationDeleted::dispatch($hotelDepositInformation);
    }
}
