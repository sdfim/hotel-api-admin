<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Modules\HotelContentRepository\Actions\HotelDepositInformation\AddHotelDepositInformation;
use Modules\HotelContentRepository\Actions\HotelDepositInformation\DeleteHotelDepositInformation;
use Modules\HotelContentRepository\Actions\HotelDepositInformation\EditHotelDepositInformation;
use Modules\HotelContentRepository\Models\HotelDepositInformation;
use Modules\HotelContentRepository\API\Requests\HotelDepositInformationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelDepositInformationController extends BaseController
{
    public function __construct(
        protected AddHotelDepositInformation $addHotelDepositInformation,
        protected EditHotelDepositInformation $editHotelDepositInformation,
        protected DeleteHotelDepositInformation $deleteHotelDepositInformation
    ) {}

    public function index()
    {
        $query = HotelDepositInformation::query();
        $query = $this->filter($query, HotelDepositInformation::class);
        $hotelDepositInformations = $query->get();

        return $this->sendResponse($hotelDepositInformations->toArray(), 'index success');
    }

    public function store(HotelDepositInformationRequest $request)
    {
        $hotelDepositInformation = $this->addHotelDepositInformation->handle($request);
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
        $hotelDepositInformation = $this->editHotelDepositInformation->handle($hotelDepositInformation, $request);
        return $this->sendResponse($hotelDepositInformation->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelDepositInformation = HotelDepositInformation::findOrFail($id);
        $this->deleteHotelDepositInformation->handle($hotelDepositInformation);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
