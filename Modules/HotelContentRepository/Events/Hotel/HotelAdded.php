<?php

namespace Modules\HotelContentRepository\Events\Hotel;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\Hotel;

class HotelAdded
{
    use Dispatchable;
    use SerializesModels;

    public $hotel;

    public function __construct(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }
}
