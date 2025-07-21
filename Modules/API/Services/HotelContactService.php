<?php

namespace Modules\API\Services;

use Modules\HotelContentRepository\Models\ContactInformation;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Product;

class HotelContactService
{
    /**
     * Get hotel contacts for a given giata code
     *
     * @param int $giataCode
     * @return array
     */
    public function getHotelContacts(int $giataCode): array
    {
        $hotel = Hotel::where('giata_code', $giataCode)->first();

        if (!$hotel) {
            return [];
        }

        $product = $hotel->product;

        if (!$product) {
            return [];
        }

        $contacts = $product->contactInformation()
            ->with(['emails', 'phones', 'ujvDepartments'])
            ->get();

        $data = $contacts->map(function ($contact) {
            return [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'job_title' => $contact->job_title,
                'emails' => $contact->emails->map(function ($email) {
                    return [
                        'email' => $email->email,
                        'departments' => $email->departments ?? [],
                    ];
                })->toArray(),
                'phones' => $contact->phones->map(function ($phone) {
                    return [
                        'country_code' => $phone->country_code,
                        'area_code' => $phone->area_code,
                        'phone' => $phone->phone,
                        'extension' => $phone->extension,
                        'description' => $phone->description,
                    ];
                })->toArray(),
                'departments' => $contact->ujvDepartments->map(function ($department) {
                    return [
                        'name' => $department->name,
                    ];
                })->toArray(),
            ];
        })->toArray();

        return $data;
    }
} 