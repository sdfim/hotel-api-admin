<?php

namespace Modules\HotelContentRepository\API\Controllers;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\API\BaseController;
use Modules\HotelContentRepository\API\Requests\HotelDescriptiveContentSectionRequest;
use Modules\HotelContentRepository\Models\HotelDescriptiveContentSection;

class HotelDescriptiveContentSectionController extends BaseController
{
    public function index()
    {
        $query = HotelDescriptiveContentSection::query();
        $query = $this->filter($query, HotelDescriptiveContentSection::class);
        $section = $query->with(['content'])->get();

        return $this->sendResponse($section->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelDescriptiveContentSectionRequest $request)
    {
        $section = HotelDescriptiveContentSection::create($request->validated());
        return $this->sendResponse($section->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $section = HotelDescriptiveContentSection::with(['content'])->findOrFail($id);
        return $this->sendResponse($section->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelDescriptiveContentSectionRequest $request, $id)
    {
        $section = HotelDescriptiveContentSection::findOrFail($id);
        $section->update($request->validated());
        return $this->sendResponse($section->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $section = HotelDescriptiveContentSection::findOrFail($id);
        $section->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
