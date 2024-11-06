<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Models\HotelAttribute;
use Modules\HotelContentRepository\API\Requests\HotelAttributeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\API\BaseController;

class HotelAttributeController extends BaseController
{
    public function index()
    {
        $query = HotelAttribute::query();
        $query = $this->applyFilters($query, HotelAttribute::class);
        $hotelAttributes = $query->get();

        return $this->sendResponse($hotelAttributes->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelAttributeRequest $request)
    {
        $hotelAttribute = HotelAttribute::create($request->validated());
        return $this->sendResponse($hotelAttribute->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelAttribute = HotelAttribute::findOrFail($id);
        return $this->sendResponse($hotelAttribute->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelAttributeRequest $request, $id)
    {
        $hotelAttribute = HotelAttribute::findOrFail($id);
        $hotelAttribute->update($request->validated());
        return $this->sendResponse($hotelAttribute->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelAttribute = HotelAttribute::findOrFail($id);
        $hotelAttribute->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
