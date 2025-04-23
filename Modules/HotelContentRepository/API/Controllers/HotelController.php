<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use Modules\HotelContentRepository\Actions\Hotel\AddHotel;
use Modules\HotelContentRepository\Actions\Hotel\DeleteHotel;
use Modules\HotelContentRepository\Actions\Hotel\EditHotel;
use Modules\HotelContentRepository\API\Requests\AttachOrDetachWebFinderRequest;
use Modules\HotelContentRepository\API\Requests\HotelRequest;
use Modules\HotelContentRepository\Models\DTOs\HotelDTO;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Transformers\CustomFractalSerializer;
use Modules\HotelContentRepository\Models\Transformers\HotelTransformer;

class HotelController extends BaseController
{
    protected Manager $fractal;

    public function __construct(
        protected AddHotel $addHotel,
        protected EditHotel $editHotel,
        protected DeleteHotel $deleteHotel,
        protected HotelDTO $hotelDTO
    ) {
        $this->fractal = app(Manager::class);
        $this->fractal->setSerializer(app(CustomFractalSerializer::class));
    }

    public function index()
    {
        $query = Hotel::query();
        $query = $this->filter($query, Hotel::class);
        $hotels = $query->get();

        $useFractal = config('packages.fractal.use_fractal', true);
        if (! $useFractal) {
            $hotelDTOs = $this->hotelDTO->transform($hotels, true);
        } else {
            $resource = new FractalCollection($hotels, new HotelTransformer);
            $hotelDTOs = $this->fractal->createData($resource)->toArray();
        }

        return $this->sendResponse($hotelDTOs, 'index success');
    }

    public function store(HotelRequest $request)
    {
        $hotel = $this->addHotel->handle($request);

        return $this->sendResponse($hotel->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show(int $id)
    {
        try {
            $hotel = Hotel::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Hotel not found', Response::HTTP_NOT_FOUND);
        }

        $useFractal = config('packages.fractal.use_fractal', true);
        if (! $useFractal) {
            $hotelDTO = $this->hotelDTO->transformHotel($hotel, true);
        } else {
            $resource = new FractalCollection([$hotel], new HotelTransformer);
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
}
