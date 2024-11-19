<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelDescriptiveContent;
use Modules\HotelContentRepository\API\Requests\HotelDescriptiveContentRequest;
use Illuminate\Http\Request;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelDescriptiveContentController extends BaseController
{
    public function index()
    {
        $query = HotelDescriptiveContent::query();
        $query = $this->filter($query, HotelDescriptiveContent::class);
        $hotelDescriptiveContents = $query->get();

        return $this->sendResponse($hotelDescriptiveContents->toArray(), 'index success');
    }

    public function store(HotelDescriptiveContentRequest $request)
    {
        $hotelDescriptiveContent = HotelDescriptiveContent::create($request->validated());
        return $this->sendResponse($hotelDescriptiveContent->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelDescriptiveContent = HotelDescriptiveContent::findOrFail($id);
        return $this->sendResponse($hotelDescriptiveContent->toArray(), 'show success');
    }

    public function update(HotelDescriptiveContentRequest $request, $id)
    {
        $hotelDescriptiveContent = HotelDescriptiveContent::findOrFail($id);
        $hotelDescriptiveContent->update($request->validated());
        return $this->sendResponse($hotelDescriptiveContent->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelDescriptiveContent = HotelDescriptiveContent::findOrFail($id);
        $hotelDescriptiveContent->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
