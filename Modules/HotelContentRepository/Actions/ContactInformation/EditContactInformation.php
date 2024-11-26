<?php

namespace Modules\HotelContentRepository\Actions\ContactInformation;

use Modules\HotelContentRepository\API\Requests\ContactInformationRequest;
use Modules\HotelContentRepository\Events\ContactInformation\ContactInformationEdited;
use Modules\HotelContentRepository\Models\ContactInformation;

class EditContactInformation
{
    public function handle(ContactInformation $hotelContactInformation, ContactInformationRequest $request)
    {
        $hotelContactInformation->update($request->validated());
        ContactInformationEdited::dispatch($hotelContactInformation);
        return $hotelContactInformation;
    }
}
