<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\KeyMapping;
use Modules\HotelContentRepository\API\Requests\KeyMappingRequest;
use Modules\API\BaseController;

class KeyMappingController extends BaseController
{
    public function index()
    {
        $query = KeyMapping::query();
        $query = $this->filter($query, KeyMapping::class);
        $keyMappings = $query->with(['keyMappingOwner'])->get();

        return $this->sendResponse($keyMappings->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(KeyMappingRequest $request)
    {
        $keyMapping = KeyMapping::create($request->validated());
        return $this->sendResponse($keyMapping->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $keyMapping = KeyMapping::with(['keyMappingOwner'])->findOrFail($id);
        return $this->sendResponse($keyMapping->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(KeyMappingRequest $request, $id)
    {
        $keyMapping = KeyMapping::findOrFail($id);
        $keyMapping->update($request->validated());
        return $this->sendResponse($keyMapping->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $keyMapping = KeyMapping::findOrFail($id);
        $keyMapping->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
