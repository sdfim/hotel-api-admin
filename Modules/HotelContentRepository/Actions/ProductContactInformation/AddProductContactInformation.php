<?php

namespace Modules\HotelContentRepository\Actions\ProductContactInformation;

use Modules\HotelContentRepository\API\Requests\ProductContactInformationRequest;
use Modules\HotelContentRepository\Events\ProductContactInformation\ProductContactInformationAdded;
use Modules\HotelContentRepository\Models\ProductContactInformation;

class AddProductContactInformation
{
    public function handle(ProductContactInformationRequest $request)
    {
        $hotelContactInformation = ProductContactInformation::create($request->validated());
        ProductContactInformationAdded::dispatch($hotelContactInformation);
        return $hotelContactInformation;
    }
}
