<?php

namespace Modules\HotelContentRepository\Actions\ProductCancellationPolicy;

use Modules\HotelContentRepository\API\Requests\ProductCancellationPolicyRequest;
use Modules\HotelContentRepository\Events\ProductCancellationPolicy\ProductCancellationPolicyAdded;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

class AddProductCancellationPolicy
{
    public function handle(ProductCancellationPolicyRequest $request)
    {
        $productCancellationPolicy = ProductCancellationPolicy::create($request->validated());
        ProductCancellationPolicyAdded::dispatch($productCancellationPolicy);
        return $productCancellationPolicy;
    }
}
