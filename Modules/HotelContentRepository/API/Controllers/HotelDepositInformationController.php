<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Modules\HotelContentRepository\Models\HotelDepositInformation;
use Modules\HotelContentRepository\API\Requests\HotelDepositInformationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelDepositInformationController extends BaseController
{
    public function index()
    {
        $query = HotelDepositInformation::query();
        $query = $this->filter($query, HotelDepositInformation::class);
        $hotelDepositInformations = $query->get();

        return $this->sendResponse($hotelDepositInformations->toArray(), 'index success');
    }

    public function store(HotelDepositInformationRequest $request)
    {
        $hotelDepositInformation = HotelDepositInformation::create($request->validated());
        return $this->sendResponse($hotelDepositInformation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelDepositInformation = HotelDepositInformation::findOrFail($id);
        return $this->sendResponse($hotelDepositInformation->toArray(), 'show success');
    }

    public function update(HotelDepositInformationRequest $request, $id)
    {
        $hotelDepositInformation = HotelDepositInformation::findOrFail($id);
        $hotelDepositInformation->update($request->validated());
        return $this->sendResponse($hotelDepositInformation->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelDepositInformation = HotelDepositInformation::findOrFail($id);
        $hotelDepositInformation->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
