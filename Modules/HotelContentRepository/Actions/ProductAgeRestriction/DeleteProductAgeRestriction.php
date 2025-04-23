<?php

namespace Modules\HotelContentRepository\Actions\ProductAgeRestriction;

use Modules\HotelContentRepository\Events\ProductAgeRestriction\ProductAgeRestrictionDeleted;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class DeleteProductAgeRestriction
{
    public function handle(ProductAgeRestriction $hotelAgeRestriction)
    {
        $hotelAgeRestriction->delete();
        ProductAgeRestrictionDeleted::dispatch($hotelAgeRestriction);
    }
}
