<?php

namespace Modules\HotelContentRepository\Events\Product;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\Product;

class ProductEdited
{
    use SerializesModels;
    use Dispatchable;

    public Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}
