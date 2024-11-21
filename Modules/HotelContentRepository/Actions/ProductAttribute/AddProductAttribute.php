<?php

namespace Modules\HotelContentRepository\Actions\ProductAttribute;

use Modules\HotelContentRepository\API\Requests\ProductAttributeRequest;
use Modules\HotelContentRepository\Events\ProductAttribute\ProductAttributeAdded;
use Modules\HotelContentRepository\Models\ProductAttribute;

class AddProductAttribute
{
    public function handle(ProductAttributeRequest $request)
    {
        $hotelAttribute = ProductAttribute::create($request->validated());
        ProductAttributeAdded::dispatch($hotelAttribute);
        return $hotelAttribute;
    }
}
