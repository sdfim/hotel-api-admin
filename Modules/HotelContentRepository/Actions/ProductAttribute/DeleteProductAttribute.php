<?php

namespace Modules\HotelContentRepository\Actions\ProductAttribute;

use Modules\HotelContentRepository\Events\ProductAttribute\ProductAttributeDeleted;
use Modules\HotelContentRepository\Models\ProductAttribute;

class DeleteProductAttribute
{
    public function handle(ProductAttribute $hotelAttribute)
    {
        $hotelAttribute->delete();
        ProductAttributeDeleted::dispatch($hotelAttribute);
    }
}
