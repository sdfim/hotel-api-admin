<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\ProductInformativeService;
use Modules\HotelContentRepository\API\Requests\ProductInformativeServiceRequest;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class ProductInformativeServiceController extends BaseController
{
    public function index()
    {
        $query = ProductInformativeService::query();
        $query = $this->filter($query, ProductInformativeService::class);
        $hotelInformativeServices = $query->get();

        return $this->sendResponse($hotelInformativeServices->toArray(), 'index success');
    }

    public function store(ProductInformativeServiceRequest $request)
    {
        $hotelInformativeService = ProductInformativeService::create($request->validated());
        return $this->sendResponse($hotelInformativeService->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelInformativeService = ProductInformativeService::findOrFail($id);
        return $this->sendResponse($hotelInformativeService->toArray(), 'show success');
    }

    public function update(ProductInformativeServiceRequest $request, $id)
    {
        $hotelInformativeService = ProductInformativeService::findOrFail($id);
        $hotelInformativeService->update($request->validated());
        return $this->sendResponse($hotelInformativeService->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelInformativeService = ProductInformativeService::findOrFail($id);
        $hotelInformativeService->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
