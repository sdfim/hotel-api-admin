<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\Vendor;

class VendorDTO
{
    public $id;
    public $name;
    public $verified;
    public $address;
    public $lat;
    public $lng;
    public $website;
    public $products;

    public function __construct(
        private readonly ProductDTO $productDTO
    ) {}

    public function transform(Collection $vendors)
    {
        return $vendors->map(function ($vendor) {
            return $this->transformVendor($vendor);
        })->all();
    }

    public function transformVendor(Vendor $vendor)
    {
        return [
            'id' => $vendor->id,
            'name' => $vendor->name,
            'verified' => $vendor->verified,
            'address' => $vendor->address,
            'lat' => $vendor->lat,
            'lng' => $vendor->lng,
            'website' => $vendor->website,
            'products' => $this->productDTO->transform($vendor->products, true),
        ];
    }
}
