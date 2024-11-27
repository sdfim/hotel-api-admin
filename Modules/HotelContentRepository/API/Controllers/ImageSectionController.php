<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\ImageSection;
use Modules\HotelContentRepository\API\Requests\ImageSectionRequest;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class ImageSectionController extends BaseController
{
    public function index()
    {
        $query = ImageSection::query();
        $query = $this->filter($query, ImageSection::class);
        $hotelImageSections = $query->get();

        return $this->sendResponse($hotelImageSections->toArray(), 'index success');
    }

    public function store(ImageSectionRequest $request)
    {
        $hotelImageSection = ImageSection::create($request->validated());
        return $this->sendResponse($hotelImageSection->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelImageSection = ImageSection::findOrFail($id);
        return $this->sendResponse($hotelImageSection->toArray(), 'show success');
    }

    public function update(ImageSectionRequest $request, $id)
    {
        $hotelImageSection = ImageSection::findOrFail($id);
        $hotelImageSection->update($request->validated());
        return $this->sendResponse($hotelImageSection->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelImageSection = ImageSection::findOrFail($id);
        $hotelImageSection->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
