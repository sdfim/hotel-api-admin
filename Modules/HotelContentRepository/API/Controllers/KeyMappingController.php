<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\KeyMappingRequest;
use Modules\HotelContentRepository\Models\KeyMapping;

class KeyMappingController extends BaseController
{
    public function index()
    {
        $query = KeyMapping::query();
        $query = $this->filter($query, KeyMapping::class);
        $keyMappings = $query->get();

        return $this->sendResponse($keyMappings->toArray(), 'index success');
    }

    public function store(KeyMappingRequest $request)
    {
        $keyMapping = KeyMapping::create($request->validated());

        return $this->sendResponse($keyMapping->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $keyMapping = KeyMapping::findOrFail($id);

        return $this->sendResponse($keyMapping->toArray(), 'show success');
    }

    public function update(KeyMappingRequest $request, $id)
    {
        $keyMapping = KeyMapping::findOrFail($id);
        $keyMapping->update($request->validated());

        return $this->sendResponse($keyMapping->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $keyMapping = KeyMapping::findOrFail($id);
        $keyMapping->delete();

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
