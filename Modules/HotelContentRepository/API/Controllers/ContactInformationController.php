<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\Actions\ContactInformation\AddContactInformation;
use Modules\HotelContentRepository\Actions\ContactInformation\DeleteContactInformation;
use Modules\HotelContentRepository\Actions\ContactInformation\EditContactInformation;
use Modules\HotelContentRepository\API\Requests\ContactInformationRequest;
use Modules\HotelContentRepository\Models\ContactInformation;

class ContactInformationController extends BaseController
{
    public function __construct(
        protected AddContactInformation $addProductContactInformation,
        protected EditContactInformation $editProductContactInformation,
        protected DeleteContactInformation $deleteProductContactInformation
    ) {}

    public function index()
    {
        $query = ContactInformation::query();
        $query = $this->filter($query, ContactInformation::class);
        $contactInformation = $query->get();

        return $this->sendResponse($contactInformation->toArray(), 'index success');
    }

    public function store(ContactInformationRequest $request)
    {
        try {
            $contactInformation = ContactInformation::create($request->validated());

            if ($request->has('emails')) {
                $emails = $request->input('emails');
                foreach ($emails as &$email) {
                    $email['contact_information_id'] = $contactInformation->id;
                }
                $contactInformation->emails()->createMany($emails);
            }

            if ($request->has('phones')) {
                $phones = $request->input('phones');
                foreach ($phones as &$phone) {
                    $phone['contact_information_id'] = $contactInformation->id;
                }
                $contactInformation->phones()->createMany($phones);
            }
        } catch (\Exception $e) {
            return $this->sendError('create failed: '.$e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse($contactInformation->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $contactInformation = ContactInformation::find($id);
        if (! $contactInformation) {
            return $this->sendError('not found', Response::HTTP_NOT_FOUND);
        }

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
