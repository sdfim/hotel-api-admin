<?php

namespace Modules\HotelContentRepository\Actions\ProductAgeRestriction;

use Modules\HotelContentRepository\API\Requests\ProductAgeRestrictionRequest;
use Modules\HotelContentRepository\Events\ProductAgeRestriction\ProductAgeRestrictionEdited;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class EditProductAgeRestriction
{
    public function handle(ProductAgeRestriction $hotelAgeRestriction, ProductAgeRestrictionRequest $request)
    {
        $hotelAgeRestriction->update($request->validated());
        ProductAgeRestrictionEdited::dispatch($hotelAgeRestriction);
        return $hotelAgeRestriction;
    }
}
