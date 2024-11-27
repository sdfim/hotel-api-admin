<?php

namespace Modules\HotelContentRepository\Actions\ContactInformation;

use Modules\HotelContentRepository\API\Requests\ContactInformationRequest;
use Modules\HotelContentRepository\Events\ContactInformation\ContactInformationAdded;
use Modules\HotelContentRepository\Models\ContactInformation;

class AddContactInformation
{
    public function handle(ContactInformationRequest $request)
    {
        $hotelContactInformation = ContactInformation::create($request->validated());
        ContactInformationAdded::dispatch($hotelContactInformation);
        return $hotelContactInformation;
    }
}
