<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\Actions\ProductCancellationPolicy\AddProductCancellationPolicy;
use Modules\HotelContentRepository\Actions\ProductCancellationPolicy\DeleteProductCancellationPolicy;
use Modules\HotelContentRepository\Actions\ProductCancellationPolicy\EditProductCancellationPolicy;
use Modules\HotelContentRepository\API\Requests\ProductCancellationPolicyRequest;
use Modules\HotelContentRepository\Models\ProductCancellationPolicy;

class ProductCancellationPolicyController extends BaseController
{
    public function __construct(
        protected AddProductCancellationPolicy $addProductCancellationPolicy,
        protected EditProductCancellationPolicy $editProductCancellationPolicy,
        protected DeleteProductCancellationPolicy $deleteProductCancellationPolicy
    ) {}

    public function index()
    {
        $query = ProductCancellationPolicy::query();
        $query = $this->filter($query, ProductCancellationPolicy::class);
        $cancellationPolicies = $query->get();

        return $this->sendResponse($cancellationPolicies->toArray(), 'index success');
    }

    public function store(ProductCancellationPolicyRequest $request)
    {
        $cancellationPolicy = $this->addProductCancellationPolicy->handle($request);

        return $this->sendResponse($cancellationPolicy->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $cancellationPolicy = ProductCancellationPolicy::findOrFail($id);

        return $this->sendResponse($cancellationPolicy->toArray(), 'show success');
    }

    public function update(ProductCancellationPolicyRequest $request, $id)
    {
        $cancellationPolicy = ProductCancellationPolicy::findOrFail($id);
        $cancellationPolicy = $this->editProductCancellationPolicy->handle($cancellationPolicy, $request);

        return $this->sendResponse($cancellationPolicy->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $cancellationPolicy = ProductCancellationPolicy::findOrFail($id);
        $this->deleteProductCancellationPolicy->handle($cancellationPolicy);

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
