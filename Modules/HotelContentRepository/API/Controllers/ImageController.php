<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\ImageRequest;
use Modules\HotelContentRepository\Models\Image;

class ImageController extends BaseController
{
    public function index()
    {
        $query = Image::query();
        $query = $this->filter($query, Image::class);
        $hotelImages = $query->get();

        return $this->sendResponse($hotelImages->toArray(), 'index success');
    }

    public function store(ImageRequest $request)
    {
        $hotelImage = Image::create($request->validated());

        return $this->sendResponse($hotelImage->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelImage = Image::findOrFail($id);

        return $this->sendResponse($hotelImage->toArray(), 'show success');
    }

    public function update(ImageRequest $request, $id)
    {
        $hotelImage = Image::findOrFail($id);
        $hotelImage->update($request->validated());

        return $this->sendResponse($hotelImage->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelImage = Image::findOrFail($id);
        $hotelImage->delete();

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
