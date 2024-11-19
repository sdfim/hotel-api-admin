<?php

namespace Modules\HotelContentRepository\Actions\HotelContactInformation;

use Modules\HotelContentRepository\API\Requests\HotelContactInformationRequest;
use Modules\HotelContentRepository\Events\HotelContactInformation\HotelContactInformationAdded;
use Modules\HotelContentRepository\Models\HotelContactInformation;

class AddHotelContactInformation
{
    public function handle(HotelContactInformationRequest $request)
    {
        $hotelContactInformation = HotelContactInformation::create($request->validated());
        HotelContactInformationAdded::dispatch($hotelContactInformation);
        return $hotelContactInformation;
    }
}
