<?php

namespace Modules\HotelContentRepository\Events\HotelAffiliation;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\HotelAffiliation;

class HotelAffiliationAdded
{
    use Dispatchable, SerializesModels;

    public $hotelAffiliation;

    public function __construct(HotelAffiliation $hotelAffiliation)
    {
        $this->hotelAffiliation = $hotelAffiliation;
    }
}
