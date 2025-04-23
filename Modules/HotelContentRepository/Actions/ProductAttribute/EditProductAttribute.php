<?php

namespace Modules\HotelContentRepository\Actions\ProductAttribute;

use Modules\HotelContentRepository\API\Requests\ProductAttributeRequest;
use Modules\HotelContentRepository\Events\ProductAttribute\ProductAttributeEdited;
use Modules\HotelContentRepository\Models\ProductAttribute;

class EditProductAttribute
{
    public function handle(ProductAttribute $hotelAttribute, ProductAttributeRequest $request)
    {
        $hotelAttribute->update($request->validated());
        ProductAttributeEdited::dispatch($hotelAttribute);
        return $hotelAttribute;
    }
}
