<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\API\Requests\HotelRoomRequest;
use Modules\HotelContentRepository\Models\HotelRoom;

class HotelRoomController extends BaseController
{
    public function index()
    {
        $query = HotelRoom::query();
        $query = $this->filter($query, HotelRoom::class);
        $hotelRooms = $query->get();

        return $this->sendResponse($hotelRooms->toArray(), 'index success');
    }

    public function store(HotelRoomRequest $request)
    {
        $hotelRoom = HotelRoom::create($request->validated());

        return $this->sendResponse($hotelRoom->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelRoom = HotelRoom::findOrFail($id);

        return $this->sendResponse($hotelRoom->toArray(), 'show success');
    }

    public function update(HotelRoomRequest $request, $id)
    {
        $hotelRoom = HotelRoom::findOrFail($id);
        $hotelRoom->update($request->validated());

        return $this->sendResponse($hotelRoom->toArray(), 'update success');
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

        return $this->sendResponse($hotelRoom->galleries->toArray(), 'Gallery attached successfully');
    }

    public function detachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotelRoom = HotelRoom::findOrFail($id);
        $hotelRoom->galleries()->detach($request->gallery_id);

        return $this->sendResponse($hotelRoom->galleries->toArray(), 'Gallery detached successfully');
    }
}
