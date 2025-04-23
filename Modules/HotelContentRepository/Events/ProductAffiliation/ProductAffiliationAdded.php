<?php

namespace Modules\HotelContentRepository\Events\ProductAffiliation;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationAdded
{
    use Dispatchable, SerializesModels;

    public $productAffiliation;

    public function __construct(ProductAffiliation $productAffiliation)
    {
        $this->hotelAffiliation = $productAffiliation;
    }
}
