<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ContactInformation;
use Illuminate\Database\Eloquent\Collection;

class ContactInformationTransformer extends TransformerAbstract
{
    public function transform($contactInformation)
    {
        if ($contactInformation instanceof Collection) {
            return $contactInformation->map(function ($item) {
                return $this->transformItem($item);
            })->toArray();
        }

        return $this->transformItem($contactInformation);
    }

    private function transformItem(ContactInformation $contactInformation)
    {
        return [
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
