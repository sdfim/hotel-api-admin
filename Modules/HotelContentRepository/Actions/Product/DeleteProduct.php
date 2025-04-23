<?php

namespace Modules\HotelContentRepository\Actions\Product;

use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Product;
use Modules\HotelContentRepository\Events\Product\ProductDeleted;

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
