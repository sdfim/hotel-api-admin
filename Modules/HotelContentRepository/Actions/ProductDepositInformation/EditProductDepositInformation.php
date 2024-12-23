<?php

namespace Modules\HotelContentRepository\Actions\ProductDepositInformation;

use Modules\HotelContentRepository\API\Requests\ProductDepositInformationRequest;
use Modules\HotelContentRepository\Events\ProductDepositInformation\ProductDepositInformationEdited;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class EditProductDepositInformation
{
    public function handle(ProductDepositInformation $productDepositInformation, ProductDepositInformationRequest $request)
    {
        $productDepositInformation->update($request->validated());
        ProductDepositInformationEdited::dispatch($productDepositInformation);
        return $productDepositInformation;
    }
}
