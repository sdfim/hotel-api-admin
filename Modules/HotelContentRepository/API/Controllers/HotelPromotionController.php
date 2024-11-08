<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\Models\HotelPromotion;
use Modules\HotelContentRepository\API\Requests\HotelPromotionRequest;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelPromotionController extends BaseController
{
    public function index()
    {
        $query = HotelPromotion::query();
        $query = $this->filter($query, HotelPromotion::class);
        $hotelPromotions = $query->with(['galleries.images'])->get();

        return $this->sendResponse($hotelPromotions->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelPromotionRequest $request)
    {
        $hotelPromotion = HotelPromotion::create($request->validated());
        return $this->sendResponse($hotelPromotion->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelPromotion = HotelPromotion::with(['galleries.images'])->findOrFail($id);
        return $this->sendResponse($hotelPromotion->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelPromotionRequest $request, $id)
    {
        $hotelPromotion = HotelPromotion::findOrFail($id);
        $hotelPromotion->update($request->validated());
        return $this->sendResponse($hotelPromotion->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelPromotion = HotelPromotion::findOrFail($id);
        $hotelPromotion->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }

    public function attachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotelPromotion = HotelPromotion::findOrFail($id);
        $hotelPromotion->galleries()->attach($request->gallery_id);
        return $this->sendResponse($hotelPromotion->galleries->toArray(), 'Gallery attached successfully', Response::HTTP_OK);
    }

    public function detachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotelPromotion = HotelPromotion::findOrFail($id);
        $hotelPromotion->galleries()->detach($request->gallery_id);
        return $this->sendResponse($hotelPromotion->galleries->toArray(), 'Gallery detached successfully', Response::HTTP_OK);
    }
}
