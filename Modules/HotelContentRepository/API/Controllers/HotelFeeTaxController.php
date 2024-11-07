<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelFeeTax;
use Modules\HotelContentRepository\API\Requests\HotelFeeTaxRequest;
use Illuminate\Http\Request;
use Modules\API\BaseController;

class HotelFeeTaxController extends BaseController
{
    public function index()
    {
        $query = HotelFeeTax::query();
        $query = $this->filter($query, HotelFeeTax::class);
        $hotelFeeTaxes = $query->get();

        return $this->sendResponse($hotelFeeTaxes->toArray(), 'index success', Response::HTTP_OK);
    }

    public function store(HotelFeeTaxRequest $request)
    {
        $hotelFeeTax = HotelFeeTax::create($request->validated());
        return $this->sendResponse($hotelFeeTax->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelFeeTax = HotelFeeTax::findOrFail($id);
        return $this->sendResponse($hotelFeeTax->toArray(), 'show success', Response::HTTP_OK);
    }

    public function update(HotelFeeTaxRequest $request, $id)
    {
        $hotelFeeTax = HotelFeeTax::findOrFail($id);
        $hotelFeeTax->update($request->validated());
        return $this->sendResponse($hotelFeeTax->toArray(), 'update success', Response::HTTP_OK);
    }

    public function destroy($id)
    {
        $hotelFeeTax = HotelFeeTax::findOrFail($id);
        $hotelFeeTax->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
