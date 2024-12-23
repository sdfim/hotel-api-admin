<?php

namespace Modules\HotelContentRepository\Actions\ProductDepositInformation;

use Modules\HotelContentRepository\API\Requests\ProductDepositInformationRequest;
use Modules\HotelContentRepository\Events\ProductDepositInformation\ProductDepositInformationAdded;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class AddProductDepositInformation
{
    public function handle(ProductDepositInformationRequest $request)
    {
        $productDepositInformation = ProductDepositInformation::create($request->validated());
        ProductDepositInformationAdded::dispatch($productDepositInformation);
        return $productDepositInformation;
    }
}
