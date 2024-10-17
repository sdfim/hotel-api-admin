<?php

namespace Modules\HotelContentRepository\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\KeyMappingOwner;
use Modules\HotelContentRepository\Http\Requests\KeyMappingOwnerRequest;
use Modules\API\BaseController;

class KeyMappingOwnerController extends BaseController
{
    public function index()
    {
        $keyMappingOwners = KeyMappingOwner::all();
        return $this->sendResponse($keyMappingOwners->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(KeyMappingOwnerRequest $request)
    {
        $keyMappingOwner = KeyMappingOwner::create($request->validated());
        return $this->sendResponse($keyMappingOwner->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $keyMappingOwner = KeyMappingOwner::findOrFail($id);
        return $this->sendResponse($keyMappingOwner->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(KeyMappingOwnerRequest $request, $id)
    {
        $keyMappingOwner = KeyMappingOwner::findOrFail($id);
        $keyMappingOwner->update($request->validated());
        return $this->sendResponse($keyMappingOwner->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $keyMappingOwner = KeyMappingOwner::findOrFail($id);
        $keyMappingOwner->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
