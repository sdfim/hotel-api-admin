<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelInformativeService;
use Modules\HotelContentRepository\API\Requests\HotelInformativeServiceRequest;
use Modules\API\BaseController;

class HotelInformativeServiceController extends BaseController
{
    public function index()
    {
        $hotelInformativeServices = HotelInformativeService::all();
        return $this->sendResponse($hotelInformativeServices->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelInformativeServiceRequest $request)
    {
        $hotelInformativeService = HotelInformativeService::create($request->validated());
        return $this->sendResponse($hotelInformativeService->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelInformativeService = HotelInformativeService::findOrFail($id);
        return $this->sendResponse($hotelInformativeService->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelInformativeServiceRequest $request, $id)
    {
        $hotelInformativeService = HotelInformativeService::findOrFail($id);
        $hotelInformativeService->update($request->validated());
        return $this->sendResponse($hotelInformativeService->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelInformativeService = HotelInformativeService::findOrFail($id);
        $hotelInformativeService->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
