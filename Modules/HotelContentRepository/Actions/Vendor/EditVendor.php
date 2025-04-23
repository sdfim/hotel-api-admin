<?php

namespace Modules\HotelContentRepository\Actions\Vendor;

use Modules\HotelContentRepository\API\Requests\VendorRequest;
use Modules\HotelContentRepository\Events\Vendor\VendorEdited;
use Modules\HotelContentRepository\Models\Vendor;

class EditVendor
{
    public function handle(Vendor $vendor, VendorRequest $request): Vendor
    {
        $vendor->update($request->validated());
        VendorEdited::dispatch($vendor);
        return $vendor;
    }
}
