<?php

namespace Modules\HotelContentRepository\Actions\ProductAgeRestriction;

use Modules\HotelContentRepository\API\Requests\ProductAgeRestrictionRequest;
use Modules\HotelContentRepository\Events\ProductAgeRestriction\ProductAgeRestrictionAdded;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;

class AddProductAgeRestriction
{
    public function handle(ProductAgeRestrictionRequest $request)
    {
        $hotelAgeRestriction = ProductAgeRestriction::create($request->validated());
        ProductAgeRestrictionAdded::dispatch($hotelAgeRestriction);
        return $hotelAgeRestriction;
    }
}
