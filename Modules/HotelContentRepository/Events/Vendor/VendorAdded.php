<?php

namespace Modules\HotelContentRepository\Events\Vendor;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\Vendor;

class VendorAdded
{
    use SerializesModels;
    use Dispatchable;

    public Vendor $vendor;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
}
