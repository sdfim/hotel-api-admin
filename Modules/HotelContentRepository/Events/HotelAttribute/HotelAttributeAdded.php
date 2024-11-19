<?php

namespace Modules\HotelContentRepository\Events\HotelAttribute;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\HotelAttribute;

class HotelAttributeAdded
{
    use Dispatchable, SerializesModels;

    public $hotelAttribute;

    public function __construct(HotelAttribute $hotelAttribute)
    {
        $this->hotelAttribute = $hotelAttribute;
    }
}
