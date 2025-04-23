<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\Actions\ProductAffiliation\AddProductAffiliation;
use Modules\HotelContentRepository\Actions\ProductAffiliation\DeleteProductAffiliation;
use Modules\HotelContentRepository\Actions\ProductAffiliation\EditProductAffiliation;
use Modules\HotelContentRepository\API\Requests\ProductAffiliationRequest;
use Modules\HotelContentRepository\Models\ProductAffiliation;

class ProductAffiliationController extends BaseController
{
    public function __construct(
        protected AddProductAffiliation $addProductAffiliation,
        protected EditProductAffiliation $editProductAffiliation,
        protected DeleteProductAffiliation $deleteProductAffiliation
    ) {}

    public function index()
    {
        $query = ProductAffiliation::query();
        $query = $this->filter($query, ProductAffiliation::class);
        $productAffiliations = $query->get();

        return $this->sendResponse($productAffiliations->toArray(), 'index success');
    }

    public function store(ProductAffiliationRequest $request)
    {
        $productAffiliation = $this->addProductAffiliation->handle($request);

        return $this->sendResponse($productAffiliation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $productAffiliation = ProductAffiliation::findOrFail($id);

        return $this->sendResponse($productAffiliation->toArray(), 'show success');
    }

    public function update(ProductAffiliationRequest $request, $id)
    {
        $productAffiliation = ProductAffiliation::findOrFail($id);
        $productAffiliation = $this->editProductAffiliation->handle($productAffiliation, $request);

        return $this->sendResponse($productAffiliation->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $productAffiliation = ProductAffiliation::findOrFail($id);
        $this->deleteProductAffiliation->handle($productAffiliation);

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
