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
        $query = ProductInformativeService::with('dynamicColumns');
        $query = $this->filter($query, ProductInformativeService::class);
        $hotelInformativeServices = $query->get();

        return $this->sendResponse($hotelInformativeServices->toArray(), 'index success');
    }

    public function store(ProductInformativeServiceRequest $request)
    {
        $hotelInformativeService = ProductInformativeService::create($request->validated());
        if ($request->has('dynamic_columns')) {
            $hotelInformativeService->dynamicColumns()->createMany($request->input('dynamic_columns'));
        }

        return $this->sendResponse($hotelInformativeService->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelInformativeService = ProductInformativeService::with('dynamicColumns')->findOrFail($id);
        return $this->sendResponse($hotelInformativeService->toArray(), 'show success');
    }

    public function update(ProductInformativeServiceRequest $request, $id)
    {
        try {
            $hotelInformativeService = ProductInformativeService::findOrFail($id);
            $hotelInformativeService->update($request->validated());

            if ($request->has('dynamic_columns')) {
                $hotelInformativeService->dynamicColumns()->delete();
                $hotelInformativeService->dynamicColumns()->createMany($request->input('dynamic_columns'));
            }
        } catch (\Exception $e) {
            return $this->sendError('update failed: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($hotelInformativeService->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelInformativeService = ProductInformativeService::findOrFail($id);
        $hotelInformativeService->delete();
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
