<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\ContactInformation;

class ContactInformationDTO
{
    public $id;
    public $contactable_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $contact_informations;

    public function __construct() {}

    public function transform(Collection $contactInformations)
    {
        return $contactInformations->map(function ($contactInformation) {
            return $this->transformContactInformation($contactInformation);
        })->all();
    }

    public function transformContactInformation(ContactInformation $contactInformation)
    {
        return [
            'id' => $contactInformation->id,
            'first_name' => $contactInformation->first_name,
            'last_name' => $contactInformation->last_name,
            'email' => $contactInformation->email,
            'phone' => $contactInformation->phone,
            'jobs' => $contactInformation->contactInformations->map(function ($information) {
                return [
                    'id' => $information->id,
                    'name' => $information->name,
                ];
            }),
        ];
    }
}
