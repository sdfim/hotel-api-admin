<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\ProductContactInformation;

class ProductContactInformationDTO
{
    public $id;
    public $product_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $contact_informations;

    public function __construct(ProductContactInformation $contactInformation)
    {
        $this->id = $contactInformation->id;
        $this->product_id = $contactInformation->product_id;
        $this->first_name = $contactInformation->first_name;
        $this->last_name = $contactInformation->last_name;
        $this->email = $contactInformation->email;
        $this->phone = $contactInformation->phone;
        $this->contact_informations = $contactInformation->contactInformations;
    }
}
