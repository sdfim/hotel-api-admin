<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Modules\HotelContentRepository\Actions\ProductAgeRestriction\AddProductAgeRestriction;
use Modules\HotelContentRepository\Actions\ProductAgeRestriction\DeleteProductAgeRestriction;
use Modules\HotelContentRepository\Actions\ProductAgeRestriction\EditProductAgeRestriction;
use Modules\HotelContentRepository\API\Controllers\BaseController;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\ProductAgeRestriction;
use Modules\HotelContentRepository\API\Requests\ProductAgeRestrictionRequest;

class ProductAgeRestrictionController extends BaseController
{
    public function __construct(
        protected AddProductAgeRestriction    $addProductAgeRestriction,
        protected EditProductAgeRestriction   $editProductAgeRestriction,
        protected DeleteProductAgeRestriction $deleteProductAgeRestriction
    ) {}

    public function index()
    {
        $query = ProductAgeRestriction::query();
        $query = $this->filter($query, ProductAgeRestriction::class);
        $restrictions = $query->get();

        return $this->sendResponse($restrictions->toArray(), 'index success');
    }

    public function store(ProductAgeRestrictionRequest $request)
    {
        $restriction = $this->addProductAgeRestriction->handle($request);
        return $this->sendResponse($restriction->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $restriction = ProductAgeRestriction::findOrFail($id);
        return $this->sendResponse($restriction->toArray(), 'show success');
    }

    public function update(ProductAgeRestrictionRequest $request, $id)
    {
        $restriction = ProductAgeRestriction::findOrFail($id);
        $restriction = $this->editProductAgeRestriction->handle($restriction, $request);
        return $this->sendResponse($restriction->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $restriction = ProductAgeRestriction::findOrFail($id);
        $this->deleteProductAgeRestriction->handle($restriction);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
