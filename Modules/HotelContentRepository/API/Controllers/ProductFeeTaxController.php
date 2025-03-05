<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\ProductFeeTaxRequest;
use Modules\HotelContentRepository\Models\ProductFeeTax;

class ProductFeeTaxController extends BaseController
{
    public function index()
    {
        $query = ProductFeeTax::query();
        $query = $this->filter($query, ProductFeeTax::class);
        $hotelFeeTaxes = $query->get();

        return $this->sendResponse($hotelFeeTaxes->toArray(), 'index success');
    }

    public function store(ProductFeeTaxRequest $request)
    {
        $hotelFeeTax = ProductFeeTax::create($request->validated());

        return $this->sendResponse($hotelFeeTax->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelFeeTax = ProductFeeTax::findOrFail($id);

        return $this->sendResponse($hotelFeeTax->toArray(), 'show success');
    }

    public function update(ProductFeeTaxRequest $request, $id)
    {
        $hotelFeeTax = ProductFeeTax::findOrFail($id);
        $hotelFeeTax->update($request->validated());

        return $this->sendResponse($hotelFeeTax->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelFeeTax = ProductFeeTax::findOrFail($id);
        $hotelFeeTax->delete();

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
