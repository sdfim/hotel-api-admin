<?php

namespace Modules\HotelContentRepository\Actions\Vendor;

use Modules\HotelContentRepository\Events\Vendor\VendorDeleted;
use Modules\HotelContentRepository\Models\Vendor;

class DeleteVendor
{
    public function handle(Vendor $vendor): void
    {
        $vendor->delete();
        VendorDeleted::dispatch($vendor);
    }
}
