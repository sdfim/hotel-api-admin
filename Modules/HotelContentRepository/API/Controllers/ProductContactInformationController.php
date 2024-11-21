<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Actions\ProductContactInformation\AddProductContactInformation;
use Modules\HotelContentRepository\Actions\ProductContactInformation\DeleteProductContactInformation;
use Modules\HotelContentRepository\Actions\ProductContactInformation\EditProductContactInformation;
use Modules\HotelContentRepository\Models\ProductContactInformation;
use Modules\HotelContentRepository\API\Requests\ProductContactInformationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class ProductContactInformationController extends BaseController
{
    public function __construct(
        protected AddProductContactInformation    $addProductContactInformation,
        protected EditProductContactInformation   $editProductContactInformation,
        protected DeleteProductContactInformation $deleteProductContactInformation
    ) {}

    public function index()
    {
        $query = ProductContactInformation::query();
        $query = $this->filter($query, ProductContactInformation::class);
        $contactInformations = $query->get();

        return $this->sendResponse($contactInformations->toArray(), 'index success');
    }

    public function store(ProductContactInformationRequest $request)
    {
        $contactInformation = $this->addProductContactInformation->handle($request);
        return $this->sendResponse($contactInformation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $contactInformation = ProductContactInformation::findOrFail($id);
        return $this->sendResponse($contactInformation->toArray(), 'show success');
    }

    public function update(ProductContactInformationRequest $request, $id)
    {
        $contactInformation = ProductContactInformation::findOrFail($id);
        $contactInformation = $this->editProductContactInformation->handle($contactInformation, $request);
        return $this->sendResponse($contactInformation->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $contactInformation = ProductContactInformation::findOrFail($id);
        $this->deleteProductContactInformation->handle($contactInformation);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
