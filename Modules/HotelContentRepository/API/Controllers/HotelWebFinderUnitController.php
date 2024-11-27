<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelWebFinderUnit;
use Modules\HotelContentRepository\API\Requests\HotelWebFinderUnitRequest;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelWebFinderUnitController extends BaseController
{
    public function index()
    {
        $query = HotelWebFinderUnit::query();
        $query = $this->filter($query, HotelWebFinderUnit::class);
        $webFinderUnits = $query->get();

        return $this->sendResponse($webFinderUnits->toArray(), 'index success');
    }

    public function store(HotelWebFinderUnitRequest $request)
    {
        $webFinderUnit = HotelWebFinderUnit::create($request->validated());
        return $this->sendResponse($webFinderUnit->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $webFinderUnit = HotelWebFinderUnit::findOrFail($id);
        return $this->sendResponse($webFinderUnit->toArray(), 'show success');
    }

    public function update(HotelWebFinderUnitRequest $request, $id)
    {
        $webFinderUnit = HotelWebFinderUnit::findOrFail($id);
        $webFinderUnit->update($request->validated());
        return $this->sendResponse($webFinderUnit->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $webFinderUnit = HotelWebFinderUnit::findOrFail($id);
        $webFinderUnit->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
