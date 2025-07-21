<?php

namespace Modules\HotelContentRepository\Actions\Product;

use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Events\Product\ProductDeleted;
use Modules\HotelContentRepository\Models\Product;

class DeleteProduct
{
    public function handle(Product $product): void
    {
        $product->delete();
        ProductDeleted::dispatch($product);
    }

    public function deleteWithRelated(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $product->related->delete();
            $product->delete();
        });
    }
}
