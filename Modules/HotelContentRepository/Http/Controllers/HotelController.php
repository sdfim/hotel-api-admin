<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Http\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\Http\Requests\HotelRequest;
use Modules\HotelContentRepository\Models\Hotel;
use Illuminate\Http\Request;
use Modules\API\BaseController;

class HotelController extends BaseController
{
    public function index()
    {
        $hotels = Hotel::with([
            'affiliations',
            'attributes',
            'contentSource',
            'roomImagesSource',
            'propertyImagesSource',
            'descriptiveContentsSection.content',
            'feeTaxes',
            'informativeServices',
            'promotions.galleries.images',
            'rooms.galleries.images',
            'keyMappings',
            'travelAgencyCommissions',
            'galleries.images'
        ])->get();
        return $this->sendResponse($hotels->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelRequest $request)
    {
        $hotel = Hotel::create($request->validated());
        return $this->sendResponse($hotel->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotel = Hotel::with([
            'affiliations',
            'attributes',
            'contentSource',
            'roomImagesSource',
            'propertyImagesSource',
            'descriptiveContentsSection.content',
            'feeTaxes',
            'informativeServices',
            'promotions.galleries.images',
            'rooms.galleries.images',
            'keyMappings',
            'travelAgencyCommissions',
            'galleries.images'
        ])->findOrFail($id);
        return $this->sendResponse($hotel->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->update($request->validated());
        return $this->sendResponse($hotel->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }

    public function attachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->galleries()->attach($request->gallery_id);
        return $this->sendResponse($hotel->galleries->toArray(), 'Gallery attached successfully', Response::HTTP_OK);
    }

    public function detachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->galleries()->detach($request->gallery_id);
        return $this->sendResponse($hotel->galleries->toArray(), 'Gallery detached successfully', Response::HTTP_OK);
    }
}
