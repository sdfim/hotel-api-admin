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

    public function createWithConditions(array $data): void
    {
        $productCancellationPolicy = ProductCancellationPolicy::create($data);

        if (isset($data['conditions'])) {
            foreach ($data['conditions'] as $condition) {
                if ($condition['compare'] == 'in' || $condition['compare'] == 'not_in') {
                    $condition['value_from'] = null;
                } else {
                    $condition['value'] = null;
                }
                $productCancellationPolicy->conditions()->create($condition);
            }
        }
    }
}
