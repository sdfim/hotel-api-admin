<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Actions\HotelAffiliation\AddHotelAffiliation;
use Modules\HotelContentRepository\Actions\HotelAffiliation\DeleteHotelAffiliation;
use Modules\HotelContentRepository\Actions\HotelAffiliation\EditHotelAffiliation;
use Modules\HotelContentRepository\Models\HotelAffiliation;
use Modules\HotelContentRepository\API\Requests\HotelAffiliationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelAffiliationController extends BaseController
{
    public function __construct(
        protected AddHotelAffiliation $addHotelAffiliation,
        protected EditHotelAffiliation $editHotelAffiliation,
        protected DeleteHotelAffiliation $deleteHotelAffiliation
    ) {}

    public function index()
    {
        $query = HotelAffiliation::query();
        $query = $this->filter($query, HotelAffiliation::class);
        $hotelAffiliations = $query->get();

        return $this->sendResponse($hotelAffiliations->toArray(), 'index success');
    }

    public function store(HotelAffiliationRequest $request)
    {
        $hotelAffiliation = $this->addHotelAffiliation->handle($request);
        return $this->sendResponse($hotelAffiliation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelAffiliation = HotelAffiliation::findOrFail($id);
        return $this->sendResponse($hotelAffiliation->toArray(), 'show success');
    }

    public function update(HotelAffiliationRequest $request, $id)
    {
        $hotelAffiliation = HotelAffiliation::findOrFail($id);
        $hotelAffiliation = $this->editHotelAffiliation->handle($hotelAffiliation, $request);
        return $this->sendResponse($hotelAffiliation->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelAffiliation = HotelAffiliation::findOrFail($id);
        $this->deleteHotelAffiliation->handle($hotelAffiliation);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
