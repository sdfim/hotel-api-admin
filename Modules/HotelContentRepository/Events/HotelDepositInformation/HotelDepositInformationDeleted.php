<?php

namespace Modules\HotelContentRepository\Events\HotelDepositInformation;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\HotelDepositInformation;

class HotelDepositInformationDeleted
{
    use Dispatchable, SerializesModels;

    public $hotelDepositInformation;

    public function __construct(HotelDepositInformation $hotelDepositInformation)
    {
        $this->hotelDepositInformation = $hotelDepositInformation;
    }
}
