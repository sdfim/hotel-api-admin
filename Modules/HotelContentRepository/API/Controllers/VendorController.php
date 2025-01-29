<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Illuminate\Http\Response;
use Modules\HotelContentRepository\Actions\Vendor\AddVendor;
use Modules\HotelContentRepository\Actions\Vendor\DeleteVendor;
use Modules\HotelContentRepository\Actions\Vendor\EditVendor;
use Modules\HotelContentRepository\API\Requests\VendorRequest;
use Modules\HotelContentRepository\Models\Vendor;

class VendorController extends BaseController
{
    public function __construct(
        protected AddVendor $addVendor,
        protected EditVendor $editVendor,
        protected DeleteVendor $deleteVendor
    ) {}

    public function index()
    {
        $query = Vendor::query();
        $query = $this->filter($query, Vendor::class);
        $vendors = $query->get();

        return $this->sendResponse($vendors->toArray(), 'index success');
    }

    public function store(VendorRequest $request)
    {
        $vendor = $this->addVendor->handle($request);

        return $this->sendResponse($vendor->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $vendor = Vendor::findOrFail($id);

        return $this->sendResponse($vendor->toArray(), 'show success');
    }

    public function update(VendorRequest $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor = $this->editVendor->handle($vendor, $request);

        return $this->sendResponse($vendor->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $this->deleteVendor->handle($vendor);

        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
