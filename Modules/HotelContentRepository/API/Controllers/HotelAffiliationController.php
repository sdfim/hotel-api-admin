<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Models\HotelAffiliation;
use Modules\HotelContentRepository\API\Requests\HotelAffiliationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelAffiliationController extends BaseController
{
    public function index()
    {
        $query = HotelAffiliation::query();
        $query = $this->filter($query, HotelAffiliation::class);
        $hotelAffiliations = $query->get();

        return $this->sendResponse($hotelAffiliations->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelAffiliationRequest $request)
    {
        $hotelAffiliation = HotelAffiliation::create($request->validated());
        return $this->sendResponse($hotelAffiliation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelAffiliation = HotelAffiliation::findOrFail($id);
        return $this->sendResponse($hotelAffiliation->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelAffiliationRequest $request, $id)
    {
        $hotelAffiliation = HotelAffiliation::findOrFail($id);
        $hotelAffiliation->update($request->validated());
        return $this->sendResponse($hotelAffiliation->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelAffiliation = HotelAffiliation::findOrFail($id);
        $hotelAffiliation->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
