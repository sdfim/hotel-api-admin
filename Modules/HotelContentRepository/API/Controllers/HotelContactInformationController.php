<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Actions\HotelContactInformation\AddHotelContactInformation;
use Modules\HotelContentRepository\Actions\HotelContactInformation\DeleteHotelContactInformation;
use Modules\HotelContentRepository\Actions\HotelContactInformation\EditHotelContactInformation;
use Modules\HotelContentRepository\Models\HotelContactInformation;
use Modules\HotelContentRepository\API\Requests\HotelContactInformationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelContactInformationController extends BaseController
{
    public function __construct(
        protected AddHotelContactInformation $addHotelContactInformation,
        protected EditHotelContactInformation $editHotelContactInformation,
        protected DeleteHotelContactInformation $deleteHotelContactInformation
    ) {}

    public function index()
    {
        $query = HotelContactInformation::query();
        $query = $this->filter($query, HotelContactInformation::class);
        $hotelContactInformations = $query->get();

        return $this->sendResponse($hotelContactInformations->toArray(), 'index success');
    }

    public function store(HotelContactInformationRequest $request)
    {
        $hotelContactInformation = $this->addHotelContactInformation->handle($request);
        return $this->sendResponse($hotelContactInformation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelContactInformation = HotelContactInformation::findOrFail($id);
        return $this->sendResponse($hotelContactInformation->toArray(), 'show success');
    }

    public function update(HotelContactInformationRequest $request, $id)
    {
        $hotelContactInformation = HotelContactInformation::findOrFail($id);
        $hotelContactInformation = $this->editHotelContactInformation->handle($hotelContactInformation, $request);
        return $this->sendResponse($hotelContactInformation->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelContactInformation = HotelContactInformation::findOrFail($id);
        $this->deleteHotelContactInformation->handle($hotelContactInformation);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
