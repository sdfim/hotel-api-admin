<?php

namespace Modules\HotelContentRepository\Actions\Product;

use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Events\Product\ProductDeleted;

class DeleteProduct
{
    public function handle(Product $product): void
    {
        $product->delete();
        ProductDeleted::dispatch($product);
    }
}
