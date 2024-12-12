<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Fractal\Resource\Collection as FractalCollection;
use Illuminate\Http\Response;
use League\Fractal\Manager;
use Modules\HotelContentRepository\Actions\Hotel\AddHotel;
use Modules\HotelContentRepository\Actions\Hotel\DeleteHotel;
use Modules\HotelContentRepository\Actions\Hotel\EditHotel;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachGalleryRequest;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachWebFinderRequest;
use Modules\HotelContentRepository\API\Requests\HotelRequest;
use Modules\HotelContentRepository\Models\DTOs\HotelDTO;
use Modules\HotelContentRepository\Models\Hotel;
use Illuminate\Http\Request;
use Modules\HotelContentRepository\API\Controllers\BaseController;
use Modules\HotelContentRepository\Models\Transformers\CustomFractalSerializer;
use Modules\HotelContentRepository\Models\Transformers\HotelTransformer;
use Spatie\Fractal\Fractal;

class HotelController extends BaseController
{
    protected Manager $fractal;

    public function __construct(
        protected AddHotel $addHotel,
        protected EditHotel $editHotel,
        protected DeleteHotel $deleteHotel,
        protected HotelDTO $hotelDTO
    ) {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new CustomFractalSerializer());
    }

    public function index()
    {
        $query = Hotel::query();
        $query = $this->filter($query, Hotel::class);
        $hotels = $query->with($this->getIncludes())->get();

        $useFractal = config('packages.use_fractal', true);
        if (!$useFractal){
            $hotelDTOs = $this->hotelDTO->transform($hotels, true);
        } else {
            $resource = new FractalCollection($hotels, new HotelTransformer());
            $hotelDTOs = $this->fractal->createData($resource)->toArray();
        }

        return $this->sendResponse($hotelDTOs, 'index success');
    }

    public function store(HotelRequest $request)
    {
        $hotel = $this->addHotel->handle($request);
        return $this->sendResponse($hotel->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        try {
            $hotel = Hotel::with($this->getIncludes())->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Hotel not found', Response::HTTP_NOT_FOUND);
        }

        $useFractal = config('packages.use_fractal', true);
        if (!$useFractal) {
            $hotelDTO = $this->hotelDTO->transform(new Collection([$hotel]), true);
        } else {
            $resource = new FractalCollection([$hotel], new HotelTransformer());
            $hotelDTO = $this->fractal->createData($resource)->toArray()[0];
        }

        return $this->sendResponse([$hotelDTO], 'show success');

    }

    public function update(HotelRequest $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel = $this->editHotel->handle($hotel, $request);
        return $this->sendResponse($hotel->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);
        $this->deleteHotel->handle($hotel);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
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
            'product.affiliations',
            'product.attributes',
            'product.contentSource',
            'product.propertyImagesSource',
            'product.descriptiveContentsSection',
            'product.feeTaxes',
            'product.informativeServices.service',
            'product.promotions.galleries.images',
            'product.keyMappings',
            'product.galleries.images',
            'product.contactInformation',

            'roomImagesSource',
            'rooms.galleries.images',
            'webFinders',
        ];
    }}
