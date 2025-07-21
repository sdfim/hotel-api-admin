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

    public function execute($data, $contactableId, $contactableType)
    {
        if ($contactableId) {
            $data['contactable_id'] = $contactableId;
        }
        $data['contactable_type'] = 'Modules\\HotelContentRepository\\Models\\'.$contactableType;
        $emails = $data['emails'] ?? [];
        $phones = $data['phones'] ?? [];
        unset($data['emails'], $data['phones']);
        $hotelContactInformation = ContactInformation::create($data);
        $hotelContactInformation->ujvDepartments()->sync($data['ujv_departments']);
        foreach ($emails as $email) {
            $email['contact_information_id'] = $hotelContactInformation->id;
            $hotelContactInformation->emails()->create($email);
        }
        foreach ($phones as $phone) {
            $phone['contact_information_id'] = $hotelContactInformation->id;
            $hotelContactInformation->phones()->create($phone);
        }
    }
}
