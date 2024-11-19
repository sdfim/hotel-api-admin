<?php

namespace Modules\HotelContentRepository\Actions\HotelAttribute;

use Modules\HotelContentRepository\API\Requests\HotelAttributeRequest;
use Modules\HotelContentRepository\Events\HotelAttribute\HotelAttributeEdited;
use Modules\HotelContentRepository\Models\HotelAttribute;

class EditHotelAttribute
{
    public function handle(HotelAttribute $hotelAttribute, HotelAttributeRequest $request)
    {
        $hotelAttribute->update($request->validated());
        HotelAttributeEdited::dispatch($hotelAttribute);
        return $hotelAttribute;
    }
}
