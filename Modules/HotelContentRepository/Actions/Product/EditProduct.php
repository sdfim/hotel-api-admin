<?php

namespace Modules\HotelContentRepository\Actions\Product;

use Modules\HotelContentRepository\API\Requests\ProductRequest;
use Modules\HotelContentRepository\Events\Product\ProductEdited;
use Modules\HotelContentRepository\Models\Product;

class EditProduct
{
    public function handle(Product $product, ProductRequest $request): Product
    {
        $product->update($request->validated());
        ProductEdited::dispatch($product);

        return $product;
    }
}
