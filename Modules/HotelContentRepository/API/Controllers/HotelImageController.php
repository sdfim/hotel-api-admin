<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelImage;
use Modules\HotelContentRepository\API\Requests\HotelImageRequest;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelImageController extends BaseController
{
    public function index()
    {
        $query = HotelImage::query();
        $query = $this->filter($query, HotelImage::class);
        $hotelImages = $query->with(['section'])->get();

        return $this->sendResponse($hotelImages->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelImageRequest $request)
    {
        $hotelImage = HotelImage::create($request->validated());
        return $this->sendResponse($hotelImage->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelImage = HotelImage::with(['section'])->findOrFail($id);
        return $this->sendResponse($hotelImage->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelImageRequest $request, $id)
    {
        $hotelImage = HotelImage::findOrFail($id);
        $hotelImage->update($request->validated());
        return $this->sendResponse($hotelImage->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelImage = HotelImage::findOrFail($id);
        $hotelImage->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
