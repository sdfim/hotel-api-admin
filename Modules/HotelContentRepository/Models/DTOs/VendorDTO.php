<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\Vendor;

class VendorDTO
{
    public $id;
    public $name;
    public $address;
    public $lat;
    public $lng;
    public $website;
    public $products;

    public function __construct(Vendor $vendor)
    {
        $this->id = $vendor->id;
        $this->name = $vendor->name;
        $this->address = $vendor->address;
        $this->lat = $vendor->lat;
        $this->lng = $vendor->lng;
        $this->website = $vendor->website;
        $this->products = $vendor->products->map(function ($product) {
            return new ProductForRelationDTO($product);
        });
    }
}
