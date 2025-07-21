<?php

namespace Modules\HotelContentRepository\Actions\Vendor;

use Modules\HotelContentRepository\API\Requests\VendorRequest;
use Modules\HotelContentRepository\Events\Vendor\VendorAdded;
use Modules\HotelContentRepository\Models\Vendor;

class AddVendor
{
    public function handle(VendorRequest $request): Vendor
    {
        $vendor = Vendor::create($request->validated());
        VendorAdded::dispatch($vendor);

        return $vendor;
    }
}
