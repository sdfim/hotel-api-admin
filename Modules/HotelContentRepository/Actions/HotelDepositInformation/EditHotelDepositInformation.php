<?php

namespace Modules\HotelContentRepository\Actions\HotelDepositInformation;

use Modules\HotelContentRepository\API\Requests\HotelDepositInformationRequest;
use Modules\HotelContentRepository\Events\HotelDepositInformation\HotelDepositInformationEdited;
use Modules\HotelContentRepository\Models\HotelDepositInformation;

class EditHotelDepositInformation
{
    public function handle(HotelDepositInformation $hotelDepositInformation, HotelDepositInformationRequest $request)
    {
        $hotelDepositInformation->update($request->validated());
        HotelDepositInformationEdited::dispatch($hotelDepositInformation);
        return $hotelDepositInformation;
    }
}
