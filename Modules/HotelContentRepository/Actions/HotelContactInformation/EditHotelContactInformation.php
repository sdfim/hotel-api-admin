<?php

namespace Modules\HotelContentRepository\Actions\HotelContactInformation;

use Modules\HotelContentRepository\API\Requests\HotelContactInformationRequest;
use Modules\HotelContentRepository\Events\HotelContactInformation\HotelContactInformationEdited;
use Modules\HotelContentRepository\Models\HotelContactInformation;

class EditHotelContactInformation
{
    public function handle(HotelContactInformation $hotelContactInformation, HotelContactInformationRequest $request)
    {
        $hotelContactInformation->update($request->validated());
        HotelContactInformationEdited::dispatch($hotelContactInformation);
        return $hotelContactInformation;
    }
}
