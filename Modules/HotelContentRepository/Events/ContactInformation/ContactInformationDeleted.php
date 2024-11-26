<?php

namespace Modules\HotelContentRepository\Events\ContactInformation;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\ContactInformation;

class ContactInformationDeleted
{
    use Dispatchable, SerializesModels;

    public $hotelContactInformation;

    public function __construct(ContactInformation $hotelContactInformation)
    {
        $this->hotelContactInformation = $hotelContactInformation;
    }
}
