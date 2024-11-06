<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Modules\API\BaseController;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;
use Modules\HotelContentRepository\API\Requests\HotelAgeRestrictionTypeRequest;

class HotelAgeRestrictionTypeController extends BaseController
{
    public function index()
    {
        $query = HotelAgeRestrictionType::query();
        $query = $this->filter($query, HotelAgeRestrictionType::class);
        $types = $query->get();

        return $this->sendResponse($types->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelAgeRestrictionTypeRequest $request)
    {
        $type = HotelAgeRestrictionType::create($request->validated());
        return $this->sendResponse($type->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $type = HotelAgeRestrictionType::findOrFail($id);
        return $this->sendResponse($type->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelAgeRestrictionTypeRequest $request, $id)
    {
        $type = HotelAgeRestrictionType::findOrFail($id);
        $type->update($request->validated());
        return $this->sendResponse($type->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $type = HotelAgeRestrictionType::findOrFail($id);
        $type->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
