<?php

namespace Modules\HotelContentRepository\Events\ProductAttribute;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\ProductAttribute;

class ProductAttributeDeleted
{
    use Dispatchable, SerializesModels;

    public $hotelAttribute;

    public function __construct(ProductAttribute $hotelAttribute)
    {
        $this->hotelAttribute = $hotelAttribute;
    }
}
