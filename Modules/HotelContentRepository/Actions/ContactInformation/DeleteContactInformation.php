<?php

namespace Modules\HotelContentRepository\Actions\ContactInformation;

use Modules\HotelContentRepository\Events\ContactInformation\ContactInformationDeleted;
use Modules\HotelContentRepository\Models\ContactInformation;

class DeleteContactInformation
{
    public function handle(ContactInformation $hotelContactInformation)
    {
        $hotelContactInformation->delete();
        ContactInformationDeleted::dispatch($hotelContactInformation);
    }
}
