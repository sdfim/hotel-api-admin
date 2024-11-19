<?php

namespace Modules\HotelContentRepository\Actions\HotelAttribute;

use Modules\HotelContentRepository\API\Requests\HotelAttributeRequest;
use Modules\HotelContentRepository\Events\HotelAttribute\HotelAttributeAdded;
use Modules\HotelContentRepository\Models\HotelAttribute;

class AddHotelAttribute
{
    public function handle(HotelAttributeRequest $request)
    {
        $hotelAttribute = HotelAttribute::create($request->validated());
        HotelAttributeAdded::dispatch($hotelAttribute);
        return $hotelAttribute;
    }
}
