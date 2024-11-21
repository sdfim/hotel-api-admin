<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\ProductContactInformation;

class ContactInformationTransformer extends TransformerAbstract
{
    public function transform(ProductContactInformation $contactInformation)
    {
        return [
            'first_name' => $contactInformation->first_name,
            'last_name' => $contactInformation->last_name,
            'email' => $contactInformation->email,
            'phone' => $contactInformation->phone,
            'job_descriptions' => $contactInformation->contactInformations,
        ];
    }
}
