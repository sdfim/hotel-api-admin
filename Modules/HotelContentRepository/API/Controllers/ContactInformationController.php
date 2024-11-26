<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Actions\ContactInformation\AddContactInformation;
use Modules\HotelContentRepository\Actions\ContactInformation\DeleteContactInformation;
use Modules\HotelContentRepository\Actions\ContactInformation\EditContactInformation;
use Modules\HotelContentRepository\Models\ContactInformation;
use Modules\HotelContentRepository\API\Requests\ContactInformationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class ContactInformationController extends BaseController
{
    public function __construct(
        protected AddContactInformation    $addProductContactInformation,
        protected EditContactInformation   $editProductContactInformation,
        protected DeleteContactInformation $deleteProductContactInformation
    ) {}

    public function index()
    {
        $query = ContactInformation::query();
        $query = $this->filter($query, ContactInformation::class);
        $contactInformations = $query->get();

        return $this->sendResponse($contactInformations->toArray(), 'index success');
    }

    public function store(ContactInformationRequest $request)
    {
        $contactInformation = $this->addProductContactInformation->handle($request);
        return $this->sendResponse($contactInformation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $contactInformation = ContactInformation::findOrFail($id);
        return $this->sendResponse($contactInformation->toArray(), 'show success');
    }

    public function update(ContactInformationRequest $request, $id)
    {
        $contactInformation = ContactInformation::findOrFail($id);
        $contactInformation = $this->editProductContactInformation->handle($contactInformation, $request);
        return $this->sendResponse($contactInformation->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $contactInformation = ContactInformation::findOrFail($id);
        $this->deleteProductContactInformation->handle($contactInformation);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
