<?php

namespace Modules\HotelContentRepository\API\Controllers;

use Modules\HotelContentRepository\Actions\HotelAgeRestriction\AddHotelAgeRestriction;
use Modules\HotelContentRepository\Actions\HotelAgeRestriction\DeleteHotelAgeRestriction;
use Modules\HotelContentRepository\Actions\HotelAgeRestriction\EditHotelAgeRestriction;
use Modules\HotelContentRepository\API\Controllers\BaseController;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\Models\HotelAgeRestriction;
use Modules\HotelContentRepository\API\Requests\HotelAgeRestrictionRequest;

class HotelAgeRestrictionController extends BaseController
{
    public function __construct(
        protected AddHotelAgeRestriction $addHotelAgeRestriction,
        protected EditHotelAgeRestriction $editHotelAgeRestriction,
        protected DeleteHotelAgeRestriction $deleteHotelAgeRestriction
    ) {}

    public function index()
    {
        $query = HotelAgeRestriction::query();
        $query = $this->filter($query, HotelAgeRestriction::class);
        $restrictions = $query->get();

        return $this->sendResponse($restrictions->toArray(), 'index success');
    }

    public function store(HotelAgeRestrictionRequest $request)
    {
        $restriction = $this->addHotelAgeRestriction->handle($request);
        return $this->sendResponse($restriction->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $restriction = HotelAgeRestriction::findOrFail($id);
        return $this->sendResponse($restriction->toArray(), 'show success');
    }

    public function update(HotelAgeRestrictionRequest $request, $id)
    {
        $restriction = HotelAgeRestriction::findOrFail($id);
        $restriction = $this->editHotelAgeRestriction->handle($restriction, $request);
        return $this->sendResponse($restriction->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $restriction = HotelAgeRestriction::findOrFail($id);
        $this->deleteHotelAgeRestriction->handle($restriction);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
