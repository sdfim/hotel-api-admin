<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Actions\ProductAttribute\AddProductAttribute;
use Modules\HotelContentRepository\Actions\ProductAttribute\DeleteProductAttribute;
use Modules\HotelContentRepository\Actions\ProductAttribute\EditProductAttribute;
use Modules\HotelContentRepository\Models\ProductAttribute;
use Modules\HotelContentRepository\API\Requests\ProductAttributeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class ProductAttributeController extends BaseController
{
    public function __construct(
        protected AddProductAttribute    $addProductAttribute,
        protected EditProductAttribute   $editProductAttribute,
        protected DeleteProductAttribute $deleteProductAttribute
    ) {}

    public function index()
    {
        $query = ProductAttribute::query();
        $query = $this->filter($query, ProductAttribute::class);
        $hotelAttributes = $query->get();

        return $this->sendResponse($hotelAttributes->toArray(), 'index success');
    }

    public function store(ProductAttributeRequest $request)
    {
        $hotelAttribute = $this->addProductAttribute->handle($request);
        return $this->sendResponse($hotelAttribute->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelAttribute = ProductAttribute::findOrFail($id);
        return $this->sendResponse($hotelAttribute->toArray(), 'show success');
    }

    public function update(ProductAttributeRequest $request, $id)
    {
        $hotelAttribute = ProductAttribute::findOrFail($id);
        $hotelAttribute = $this->editProductAttribute->handle($hotelAttribute, $request);
        return $this->sendResponse($hotelAttribute->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelAttribute = ProductAttribute::findOrFail($id);
        $this->deleteProductAttribute->handle($hotelAttribute);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
