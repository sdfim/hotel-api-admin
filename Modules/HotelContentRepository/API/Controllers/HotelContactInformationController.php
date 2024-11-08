<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Models\HotelContactInformation;
use Modules\HotelContentRepository\API\Requests\HotelContactInformationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelContactInformationController extends BaseController
{
    public function index()
    {
        $query = HotelContactInformation::query();
        $query = $this->filter($query, HotelContactInformation::class);
        $hotelContactInformations = $query->get();

        return $this->sendResponse($hotelContactInformations->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelContactInformationRequest $request)
    {
        $hotelContactInformation = HotelContactInformation::create($request->validated());
        return $this->sendResponse($hotelContactInformation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelContactInformation = HotelContactInformation::findOrFail($id);
        return $this->sendResponse($hotelContactInformation->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelContactInformationRequest $request, $id)
    {
        $hotelContactInformation = HotelContactInformation::findOrFail($id);
        $hotelContactInformation->update($request->validated());
        return $this->sendResponse($hotelContactInformation->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelContactInformation = HotelContactInformation::findOrFail($id);
        $hotelContactInformation->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
