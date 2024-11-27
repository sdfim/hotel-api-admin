<?php

namespace Modules\HotelContentRepository\Events\ProductAgeRestriction;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class ProductAgeRestrictionEdited
{
    use Dispatchable, SerializesModels;

    public $hotelAgeRestriction;

    public function __construct(ProductAgeRestriction $hotelAgeRestriction)
    {
        $this->hotelAgeRestriction = $hotelAgeRestriction;
    }
}
