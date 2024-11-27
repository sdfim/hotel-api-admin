<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;
use Modules\HotelContentRepository\API\Requests\ProductDescriptiveContentSectionRequest;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class ProductDescriptiveContentSectionController extends BaseController
{
    public function index()
    {
        $query = ProductDescriptiveContentSection::query();
        $query = $this->filter($query, ProductDescriptiveContentSection::class);
        $section = $query->with(['content'])->get();

        return $this->sendResponse($section->toArray(), 'index success');
    }

    public function store(ProductDescriptiveContentSectionRequest $request)
    {
        $section = ProductDescriptiveContentSection::create($request->validated());
        return $this->sendResponse($section->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $section = ProductDescriptiveContentSection::with(['content'])->findOrFail($id);
        return $this->sendResponse($section->toArray(), 'show success');
    }

    public function update(ProductDescriptiveContentSectionRequest $request, $id)
    {
        $section = ProductDescriptiveContentSection::findOrFail($id);
        $section->update($request->validated());
        return $this->sendResponse($section->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $section = ProductDescriptiveContentSection::findOrFail($id);
        $section->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
