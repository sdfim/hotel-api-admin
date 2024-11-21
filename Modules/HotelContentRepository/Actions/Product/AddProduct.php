<?php

namespace Modules\HotelContentRepository\Actions\Product;

use Modules\HotelContentRepository\Events\Product\ProductAdded;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\API\Requests\ProductRequest;

class AddProduct
{
    public function handle(ProductRequest $request): Product
    {
        $product = Product::create($request->validated());
        ProductAdded::dispatch($product);
        return $product;
    }
}
