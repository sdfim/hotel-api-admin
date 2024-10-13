<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Http\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Http\Requests\HotelRoomRequest;
use Modules\API\BaseController;

class HotelRoomController extends BaseController
{
    public function index()
    {
        $hotelRooms = HotelRoom::with(['galleries.images'])->get();
        return $this->sendResponse($hotelRooms->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelRoomRequest $request)
    {
        $hotelRoom = HotelRoom::create($request->validated());
        return $this->sendResponse($hotelRoom->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelRoom = HotelRoom::with(['galleries.images'])->findOrFail($id);
        return $this->sendResponse($hotelRoom->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelRoomRequest $request, $id)
    {
        $hotelRoom = HotelRoom::findOrFail($id);
        $hotelRoom->update($request->validated());
        return $this->sendResponse($hotelRoom->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelRoom = HotelRoom::findOrFail($id);
        $hotelRoom->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }

    public function attachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotelRoom = HotelRoom::findOrFail($id);
        $hotelRoom->galleries()->attach($request->gallery_id);
        return $this->sendResponse($hotelRoom->galleries->toArray(), 'Gallery attached successfully', Response::HTTP_OK);
    }

    public function detachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotelRoom = HotelRoom::findOrFail($id);
        $hotelRoom->galleries()->detach($request->gallery_id);
        return $this->sendResponse($hotelRoom->galleries->toArray(), 'Gallery detached successfully', Response::HTTP_OK);
    }
}
