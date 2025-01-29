<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachImageRequest;
use Modules\HotelContentRepository\API\Requests\ImageGalleryRequest;
use Modules\HotelContentRepository\Models\ImageGallery;

class ImageGalleryController extends BaseController
{
    public function index()
    {
        $query = ImageGallery::query();
        $query = $this->filter($query, ImageGallery::class);
        $galleries = $query->get();

        return $this->sendResponse($galleries->toArray(), 'index success');
    }

    public function store(ImageGalleryRequest $request)
    {
        $gallery = ImageGallery::create($request->validated());

        return $this->sendResponse($gallery->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $gallery = ImageGallery::findOrFail($id);

        return $this->sendResponse($gallery->toArray(), 'show success');
    }

    public function update(ImageGalleryRequest $request, $id)
    {
        $gallery = ImageGallery::findOrFail($id);
        $gallery->update($request->validated());

        return $this->sendResponse($gallery->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $gallery = ImageGallery::findOrFail($id);
        $gallery->delete();

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }

    public function attachImage(AttachOrDetachImageRequest $request, $id)
    {
        $gallery = ImageGallery::findOrFail($id);
        $imageId = $request->input('image_id');
        $gallery->images()->attach($imageId);

        return $this->sendResponse($gallery->load('images')->toArray(), 'Image attached successfully');
    }

    public function detachImage(AttachOrDetachImageRequest $request, $id)
    {
        $gallery = ImageGallery::findOrFail($id);
        $imageId = $request->input('image_id');
        $gallery->images()->detach($imageId);

        return $this->sendResponse($gallery->load('images')->toArray(), 'Image detached successfully');
    }
}
