<?php

namespace Modules\HotelContentRepository\Actions\HotelAgeRestriction;

use Modules\HotelContentRepository\API\Requests\HotelAgeRestrictionRequest;
use Modules\HotelContentRepository\Events\HotelAgeRestriction\HotelAgeRestrictionEdited;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;

class EditHotelAgeRestriction
{
    public function handle(HotelAgeRestriction $hotelAgeRestriction, HotelAgeRestrictionRequest $request)
    {
        $hotelAgeRestriction->update($request->validated());
        HotelAgeRestrictionEdited::dispatch($hotelAgeRestriction);
        return $hotelAgeRestriction;
    }
}
