<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelAgeRestrictionType;
use Modules\HotelContentRepository\API\Requests\HotelAgeRestrictionTypeRequest;

class HotelAgeRestrictionTypeController extends Controller
{
    public function index()
    {
        $types = HotelAgeRestrictionType::all();
        return response()->json(['data' => $types], Response::HTTP_OK);
    }

    public function store(HotelAgeRestrictionTypeRequest $request)
    {
        $type = HotelAgeRestrictionType::create($request->validated());
        return response()->json(['data' => $type], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $type = HotelAgeRestrictionType::findOrFail($id);
        return response()->json(['data' => $type], Response::HTTP_OK);
    }

    public function update(HotelAgeRestrictionTypeRequest $request, $id)
    {
        $type = HotelAgeRestrictionType::findOrFail($id);
        $type->update($request->validated());
        return response()->json(['data' => $type], Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $type = HotelAgeRestrictionType::findOrFail($id);
        $type->delete();
        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
