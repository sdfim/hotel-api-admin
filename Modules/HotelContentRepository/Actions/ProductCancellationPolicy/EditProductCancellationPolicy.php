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

    public function updateWithConditions(ProductCancellationPolicy $productCancellationPolicy, array $data): void
    {
        $productCancellationPolicy->update($data);

        if (isset($data['conditions'])) {
            foreach ($data['conditions'] as $condition) {
                if ($condition['compare'] == 'in' || $condition['compare'] == 'not_in') {
                    $condition['value_from'] = null;
                } else {
                    $condition['value'] = null;
                }
                if (isset($condition['id'])) {
                    $productCancellationPolicy->conditions()->updateOrCreate(['id' => $condition['id']], $condition);
                } else {
                    $productCancellationPolicy->conditions()->create($condition);
                }
            }
        }
    }
}
