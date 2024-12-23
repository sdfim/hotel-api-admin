<?php

namespace Modules\HotelContentRepository\Actions\ProductCancellationPolicy;

use Modules\HotelContentRepository\API\Requests\ProductCancellationPolicyRequest;
use Modules\HotelContentRepository\Events\ProductCancellationPolicy\ProductCancellationPolicyEdited;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

class EditProductCancellationPolicy
{
    public function handle(ProductCancellationPolicy $productCancellationPolicy, ProductCancellationPolicyRequest $request)
    {
        $productCancellationPolicy->update($request->validated());
        ProductCancellationPolicyEdited::dispatch($productCancellationPolicy);
        return $productCancellationPolicy;
    }
}
