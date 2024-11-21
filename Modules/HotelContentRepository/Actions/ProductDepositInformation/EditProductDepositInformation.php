<?php

namespace Modules\HotelContentRepository\Actions\ProductDepositInformation;

use Modules\HotelContentRepository\API\Requests\ProductDepositInformationRequest;
use Modules\HotelContentRepository\Events\ProductDepositInformation\ProductDepositInformationEdited;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class EditProductDepositInformation
{
    public function handle(ProductDepositInformation $hotelDepositInformation, ProductDepositInformationRequest $request)
    {
        $hotelDepositInformation->update($request->validated());
        ProductDepositInformationEdited::dispatch($hotelDepositInformation);
        return $hotelDepositInformation;
    }
}
