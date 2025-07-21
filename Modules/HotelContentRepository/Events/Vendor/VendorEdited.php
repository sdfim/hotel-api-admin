<?php

namespace Modules\HotelContentRepository\Events\Vendor;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\Vendor;

class VendorEdited
{
    use Dispatchable;
    use SerializesModels;

    public Vendor $vendor;

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }
}
