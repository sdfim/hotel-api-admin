<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\ProductInformativeServiceRequest;
use Modules\HotelContentRepository\Models\ProductInformativeService;
use Modules\HotelContentRepository\Models\Traits\ProductResponseTransformable;

class ProductInformativeServiceController extends BaseController
{
    use ProductResponseTransformable;

    public function index()
    {
        $query = ProductInformativeService::query();
        $query = $this->filter($query, ProductInformativeService::class);
        $response = $this->filterAndTransformProductResponse($query);

        return $this->sendResponse($response->toArray(), 'index success');
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
        $hotelInformativeService = ProductInformativeService::findOrFail($id);

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
            return $this->sendError('update failed: '.$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
