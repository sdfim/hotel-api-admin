<?php

namespace Modules\HotelContentRepository\Events\HotelContactInformation;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\HotelContactInformation;

class HotelContactInformationAdded
{
    use Dispatchable, SerializesModels;

    public $hotelContactInformation;

    public function __construct(HotelContactInformation $hotelContactInformation)
    {
        $this->hotelContactInformation = $hotelContactInformation;
    }
}
