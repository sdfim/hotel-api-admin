<?php

namespace Modules\HotelContentRepository\Actions\HotelDepositInformation;

use Modules\HotelContentRepository\API\Requests\HotelDepositInformationRequest;
use Modules\HotelContentRepository\Events\HotelDepositInformation\HotelDepositInformationAdded;
use Modules\HotelContentRepository\Models\HotelDepositInformation;

class AddHotelDepositInformation
{
    public function handle(HotelDepositInformationRequest $request)
    {
        $hotelDepositInformation = HotelDepositInformation::create($request->validated());
        HotelDepositInformationAdded::dispatch($hotelDepositInformation);
        return $hotelDepositInformation;
    }
}
