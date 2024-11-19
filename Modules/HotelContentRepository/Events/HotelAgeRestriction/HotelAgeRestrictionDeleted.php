<?php

namespace Modules\HotelContentRepository\Events\HotelAgeRestriction;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;

class HotelAgeRestrictionDeleted
{
    use Dispatchable, SerializesModels;

    public $hotelAgeRestriction;

    public function __construct(HotelAgeRestriction $hotelAgeRestriction)
    {
        $this->hotelAgeRestriction = $hotelAgeRestriction;
    }
}
