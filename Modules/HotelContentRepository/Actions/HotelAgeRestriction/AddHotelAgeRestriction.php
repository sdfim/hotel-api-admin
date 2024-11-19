<?php

namespace Modules\HotelContentRepository\Actions\HotelAgeRestriction;

use Modules\HotelContentRepository\API\Requests\HotelAgeRestrictionRequest;
use Modules\HotelContentRepository\Events\HotelAgeRestriction\HotelAgeRestrictionAdded;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;

class AddHotelAgeRestriction
{
    public function handle(HotelAgeRestrictionRequest $request)
    {
        $hotelAgeRestriction = HotelAgeRestriction::create($request->validated());
        HotelAgeRestrictionAdded::dispatch($hotelAgeRestriction);
        return $hotelAgeRestriction;
    }
}
