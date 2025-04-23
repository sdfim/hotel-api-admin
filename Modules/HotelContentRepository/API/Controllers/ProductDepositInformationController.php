<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\Actions\ProductDepositInformation\AddProductDepositInformation;
use Modules\HotelContentRepository\Actions\ProductDepositInformation\DeleteProductDepositInformation;
use Modules\HotelContentRepository\Actions\ProductDepositInformation\EditProductDepositInformation;
use Modules\HotelContentRepository\API\Requests\ProductDepositInformationRequest;
use Modules\HotelContentRepository\Models\ProductDepositInformation;

class ProductDepositInformationController extends BaseController
{
    public function __construct(
        protected AddProductDepositInformation $addProductDepositInformation,
        protected EditProductDepositInformation $editProductDepositInformation,
        protected DeleteProductDepositInformation $deleteProductDepositInformation
    ) {}

    public function index()
    {
        $query = ProductDepositInformation::query();
        $query = $this->filter($query, ProductDepositInformation::class);
        $hotelDepositInformation = $query->get();

        return $this->sendResponse($hotelDepositInformation->toArray(), 'index success');
    }

    public function store(ProductDepositInformationRequest $request)
    {
        $hotelDepositInformation = $this->addProductDepositInformation->handle($request);

        return $this->sendResponse($hotelDepositInformation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelDepositInformation = ProductDepositInformation::findOrFail($id);

        return $this->sendResponse($hotelDepositInformation->toArray(), 'show success');
    }

    public function update(ProductDepositInformationRequest $request, $id)
    {
        $hotelDepositInformation = ProductDepositInformation::findOrFail($id);
        $hotelDepositInformation = $this->editProductDepositInformation->handle($hotelDepositInformation, $request);

        return $this->sendResponse($hotelDepositInformation->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelDepositInformation = ProductDepositInformation::findOrFail($id);
        $this->deleteProductDepositInformation->handle($hotelDepositInformation);

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
