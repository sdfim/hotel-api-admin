<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\HotelWebFinderRequest;
use Modules\HotelContentRepository\Models\HotelWebFinder;

class HotelWebFinderController extends BaseController
{
    public function index()
    {
        $query = HotelWebFinder::query();
        $query = $this->filter($query, HotelWebFinder::class);
        $webFinders = $query->get();

        return $this->sendResponse($webFinders->toArray(), 'index success');
    }

    public function store(HotelWebFinderRequest $request)
    {
        $webFinder = HotelWebFinder::create($request->validated());

        return $this->sendResponse($webFinder->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $webFinder = HotelWebFinder::findOrFail($id);

        return $this->sendResponse($webFinder->toArray(), 'show success');
    }

    public function update(HotelWebFinderRequest $request, $id)
    {
        $webFinder = HotelWebFinder::findOrFail($id);
        $webFinder->update($request->validated());

        return $this->sendResponse($webFinder->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $webFinder = HotelWebFinder::findOrFail($id);
        $webFinder->delete();

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
