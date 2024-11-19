<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachWebFinderRequest;
use Modules\HotelContentRepository\API\Requests\HotelRequest;
use Modules\HotelContentRepository\Models\DTOs\HotelDTO;
use Modules\HotelContentRepository\Models\Hotel;
use Illuminate\Http\Request;
use Modules\HotelContentRepository\API\Controllers\BaseController;
use Modules\HotelContentRepository\Models\Transformers\HotelTransformer;
use Spatie\Fractal\Fractal;

class HotelController extends BaseController
{
    public function index()
    {
        $query = Hotel::query();
        $query = $this->filter($query, Hotel::class);
        $hotels = $query->with($this->getIncludes())->get();

        $useFractal = env('USE_FRACTAL', true);
        if (!$useFractal){
            $hotelDTOs = $hotels->map(function ($hotel) {
                return new HotelDTO($hotel);
            })->toArray();
        } else {
            $hotelDTOs = Fractal::create()
                ->collection($hotels)
                ->transformWith(new HotelTransformer())
                ->toArray()['data'];
        }

        return $this->sendResponse($hotelDTOs, 'index success');
    }

    public function store(HotelRequest $request)
    {
        $hotel = Hotel::create($request->validated());
        return $this->sendResponse($hotel->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotel = Hotel::with($this->getIncludes())->findOrFail($id);

        $useFractal = env('USE_FRACTAL', true);
        if (!$useFractal){
            $hotelDTO = new HotelDTO($hotel);
        } else {
            $hotelDTO = Fractal::create()
                ->item($hotel)
                ->transformWith(new HotelTransformer())
                ->toArray()['data'];
        }

        return $this->sendResponse([$hotelDTO], 'show success');

    }

    public function update(HotelRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->update($request->validated());
        return $this->sendResponse($hotel->toArray(), 'update success');
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
        return $this->sendResponse($hotel->galleries->toArray(), 'Gallery attached successfully');
    }

    public function detachGallery(AttachOrDetachGalleryRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->galleries()->detach($request->gallery_id);
        return $this->sendResponse($hotel->galleries->toArray(), 'Gallery detached successfully');
    }

    public function attachWebFinder(AttachOrDetachWebFinderRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->webFinders()->attach($request->web_finder_id);
        return $this->sendResponse($hotel->webFinders->toArray(), 'Web Finder attached successfully');
    }

    public function detachWebFinder(AttachOrDetachWebFinderRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->webFinders()->detach($request->web_finder_id);
        return $this->sendResponse($hotel->webFinders->toArray(), 'Web Finder detached successfully');
    }

    protected function getIncludes(): array
    {
        return [
            'affiliations',
            'attributes',
            'contentSource',
            'roomImagesSource',
            'propertyImagesSource',
            'descriptiveContentsSection.content',
            'feeTaxes',
            'informativeServices.service',
            'promotions.galleries.images',
            'rooms.galleries.images',
            'keyMappings',
            'galleries.images',
            'contactInformation',
            'webFinders',
        ];
    }}
