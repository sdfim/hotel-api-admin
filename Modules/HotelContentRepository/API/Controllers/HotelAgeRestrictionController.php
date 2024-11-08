<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Modules\HotelContentRepository\API\Controllers\BaseController;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;
use Modules\HotelContentRepository\API\Requests\HotelAgeRestrictionRequest;

class HotelAgeRestrictionController extends BaseController
{
    public function index()
    {
        $query = HotelAgeRestriction::query();
        $query = $this->filter($query, HotelAgeRestriction::class);
        $restrictions = $query->get();

        return $this->sendResponse($restrictions->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelAgeRestrictionRequest $request)
    {
        $restriction = HotelAgeRestriction::create($request->validated());
        return $this->sendResponse($restriction->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $restriction = HotelAgeRestriction::findOrFail($id);
        return $this->sendResponse($restriction->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelAgeRestrictionRequest $request, $id)
    {
        $restriction = HotelAgeRestriction::findOrFail($id);
        $restriction->update($request->validated());
        return $this->sendResponse($restriction->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $restriction = HotelAgeRestriction::findOrFail($id);
        $restriction->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
