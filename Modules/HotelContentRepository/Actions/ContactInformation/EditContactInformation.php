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

    public function execute($data, $record, $contactableType)
    {
        $data['contactable_type'] = 'Modules\\HotelContentRepository\\Models\\'.$contactableType;
        $emails = $data['emails'] ?? [];
        $phones = $data['phones'] ?? [];
        unset($data['emails'], $data['phones']);
        $record->update($data);
        $record->ujvDepartments()->sync($data['ujv_departments']);
        $record->emails()->delete();
        foreach ($emails as $email) {
            $email['contact_information_id'] = $record->id;
            $record->emails()->create($email);
        }
        $record->phones()->delete();
        foreach ($phones as $phone) {
            $phone['contact_information_id'] = $record->id;
            $record->phones()->create($phone);
        }
    }
}
