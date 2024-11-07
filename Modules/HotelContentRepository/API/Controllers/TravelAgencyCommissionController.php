<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;
use Modules\HotelContentRepository\API\Requests\TravelAgencyCommissionRequest;
use Modules\API\BaseController;

class TravelAgencyCommissionController extends BaseController
{
    public function index()
    {
        $query = TravelAgencyCommission::query();
        $query = $this->filter($query, TravelAgencyCommission::class);
        $commissions = $query->get();

        return $this->sendResponse($commissions->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(TravelAgencyCommissionRequest $request)
    {
        $commission = TravelAgencyCommission::create($request->validated());
        return $this->sendResponse($commission->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $commission = TravelAgencyCommission::findOrFail($id);
        return $this->sendResponse($commission->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(TravelAgencyCommissionRequest $request, $id)
    {
        $commission = TravelAgencyCommission::findOrFail($id);
        $commission->update($request->validated());
        return $this->sendResponse($commission->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $commission = TravelAgencyCommission::findOrFail($id);
        $commission->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
