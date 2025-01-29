<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\API\Requests\ProductPromotionRequest;
use Modules\HotelContentRepository\Models\ProductPromotion;

class ProductPromotionController extends BaseController
{
    public function index()
    {
        $query = ProductPromotion::query();
        $query = $this->filter($query, ProductPromotion::class);
        $productPromotions = $query->get();

        return $this->sendResponse($productPromotions->toArray(), 'index success');
    }

    public function store(ProductPromotionRequest $request)
    {
        $hotelPromotion = ProductPromotion::create($request->validated());

        return $this->sendResponse($hotelPromotion->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelPromotion = ProductPromotion::findOrFail($id);

        return $this->sendResponse($hotelPromotion->toArray(), 'show success');
    }

    public function update(ProductPromotionRequest $request, $id)
    {
        $hotelPromotion = ProductPromotion::findOrFail($id);
        $hotelPromotion->update($request->validated());

        return $this->sendResponse($hotelPromotion->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelPromotion = ProductPromotion::findOrFail($id);
        $hotelPromotion->delete();

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }

    public function attachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotelPromotion = ProductPromotion::findOrFail($id);
        $hotelPromotion->galleries()->attach($request->gallery_id);

        return $this->sendResponse($hotelPromotion->galleries->toArray(), 'Gallery attached successfully');
    }

    public function detachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotelPromotion = ProductPromotion::findOrFail($id);
        $hotelPromotion->galleries()->detach($request->gallery_id);

        return $this->sendResponse($hotelPromotion->galleries->toArray(), 'Gallery detached successfully');
    }
}
