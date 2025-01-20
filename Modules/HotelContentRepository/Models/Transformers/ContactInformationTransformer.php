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
            'jobs' => $contactInformation->contactInformations->map(function ($information) {
                return [
                    'id' => $information->id,
                    'name' => $information->name,
                ];
            }),
        ];
    }
}
