<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\ProductDescriptiveContent;
use Modules\HotelContentRepository\API\Requests\ProductDescriptiveContentRequest;
use Illuminate\Http\Request;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class ProductDescriptiveContentController extends BaseController
{
    public function index()
    {
        $query = ProductDescriptiveContent::query();
        $query = $this->filter($query, ProductDescriptiveContent::class);
        $hotelDescriptiveContents = $query->get();

        return $this->sendResponse($hotelDescriptiveContents->toArray(), 'index success');
    }

    public function store(ProductDescriptiveContentRequest $request)
    {
        $hotelDescriptiveContent = ProductDescriptiveContent::create($request->validated());
        return $this->sendResponse($hotelDescriptiveContent->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelDescriptiveContent = ProductDescriptiveContent::findOrFail($id);
        return $this->sendResponse($hotelDescriptiveContent->toArray(), 'show success');
    }

    public function update(ProductDescriptiveContentRequest $request, $id)
    {
        $hotelDescriptiveContent = ProductDescriptiveContent::findOrFail($id);
        $hotelDescriptiveContent->update($request->validated());
        return $this->sendResponse($hotelDescriptiveContent->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelDescriptiveContent = ProductDescriptiveContent::findOrFail($id);
        $hotelDescriptiveContent->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
