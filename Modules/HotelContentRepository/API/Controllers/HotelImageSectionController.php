<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelImageSection;
use Modules\HotelContentRepository\API\Requests\HotelImageSectionRequest;
use Modules\API\BaseController;

class HotelImageSectionController extends BaseController
{
    public function index()
    {
        $query = HotelImageSection::query();
        $query = $this->applyFilters($query, HotelImageSection::class);
        $hotelImageSections = $query->get();

        return $this->sendResponse($hotelImageSections->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelImageSectionRequest $request)
    {
        $hotelImageSection = HotelImageSection::create($request->validated());
        return $this->sendResponse($hotelImageSection->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelImageSection = HotelImageSection::findOrFail($id);
        return $this->sendResponse($hotelImageSection->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelImageSectionRequest $request, $id)
    {
        $hotelImageSection = HotelImageSection::findOrFail($id);
        $hotelImageSection->update($request->validated());
        return $this->sendResponse($hotelImageSection->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelImageSection = HotelImageSection::findOrFail($id);
        $hotelImageSection->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
