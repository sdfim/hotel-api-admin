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
            'job_title' => $contactInformation->job_title,
            'contactable_id' => $contactInformation->contactable_id,
            'contactable_type' => $contactInformation->contactable_type,
            'ujv_department' => $contactInformation->ujv_department,
            'emails' => $contactInformation->emails->map(function ($email) {
                return [
                    'email' => $email->email,
                ];
            }),
            'phones' => $contactInformation->phones->map(function ($phone) {
                return [
                    'phone' => $phone->phone,
                ];
            }),
        ];
    }
}
