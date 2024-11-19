<?php

namespace Modules\HotelContentRepository\Events\HotelContactInformation;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\HotelContactInformation;

class HotelContactInformationEdited
{
    use Dispatchable, SerializesModels;

    public $hotelContactInformation;

    public function __construct(HotelContactInformation $hotelContactInformation)
    {
        $this->hotelContactInformation = $hotelContactInformation;
    }
}
