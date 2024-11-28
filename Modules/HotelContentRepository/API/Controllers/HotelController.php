<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
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
use Modules\HotelContentRepository\Models\Transformers\HotelTransformer;
use Spatie\Fractal\Fractal;

class HotelController extends BaseController
{
    public function __construct(
        protected AddHotel $addHotel,
        protected EditHotel $editHotel,
        protected DeleteHotel $deleteHotel,
        protected HotelDTO $hotelDTO
    ) {}

    public function index()
    {
        $query = Hotel::query();
        $query = $this->filter($query, Hotel::class);
        $hotels = $query->with($this->getIncludes())->get();

        $useFractal = env('USE_FRACTAL', true);
        if (!$useFractal){
            $hotelDTOs = $this->hotelDTO->transform($hotels, true);
        } else {
            $hotelDTOs = Fractal::create()
                ->collection($hotels, new HotelTransformer())
                ->toArray()['data'];
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
        $hotel = Hotel::with($this->getIncludes())->findOrFail($id);

        $useFractal = env('USE_FRACTAL', true);
        if (!$useFractal) {
            $hotelDTO = $this->hotelDTO->transform(new Collection([$hotel]), true);
        } else {
            $hotelDTO = Fractal::create()->item($hotel, new HotelTransformer());
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
            'product.descriptiveContentsSection.content',
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
