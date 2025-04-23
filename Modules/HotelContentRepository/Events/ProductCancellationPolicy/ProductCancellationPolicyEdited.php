<?php

namespace Modules\HotelContentRepository\Events\ProductCancellationPolicy;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

class ProductCancellationPolicyEdited
{
    use Dispatchable;
    use SerializesModels;

    public ProductCancellationPolicy $productCancellationPolicy;

    public function __construct(ProductCancellationPolicy $productCancellationPolicy)
    {
        $this->productCancellationPolicy = $productCancellationPolicy;
    }
}
