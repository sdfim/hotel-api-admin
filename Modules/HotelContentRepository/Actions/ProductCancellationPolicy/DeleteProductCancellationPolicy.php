<?php

namespace Modules\HotelContentRepository\Actions\ProductCancellationPolicy;

use Modules\HotelContentRepository\Events\ProductCancellationPolicy\ProductCancellationPolicyDeleted;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

class DeleteProductCancellationPolicy
{
    public function handle(ProductCancellationPolicy $productCancellationPolicy)
    {
        $productCancellationPolicy->delete();
        ProductCancellationPolicyDeleted::dispatch($productCancellationPolicy);
    }
}
