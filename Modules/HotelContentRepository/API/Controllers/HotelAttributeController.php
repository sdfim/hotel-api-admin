<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Http\Controllers\Controller;
use Modules\HotelContentRepository\Actions\HotelAttribute\AddHotelAttribute;
use Modules\HotelContentRepository\Actions\HotelAttribute\DeleteHotelAttribute;
use Modules\HotelContentRepository\Actions\HotelAttribute\EditHotelAttribute;
use Modules\HotelContentRepository\Models\HotelAttribute;
use Modules\HotelContentRepository\API\Requests\HotelAttributeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class HotelAttributeController extends BaseController
{
    public function __construct(
        protected AddHotelAttribute $addHotelAttribute,
        protected EditHotelAttribute $editHotelAttribute,
        protected DeleteHotelAttribute $deleteHotelAttribute
    ) {}

    public function index()
    {
        $query = HotelAttribute::query();
        $query = $this->filter($query, HotelAttribute::class);
        $hotelAttributes = $query->get();

        return $this->sendResponse($hotelAttributes->toArray(), 'index success');
    }

    public function store(HotelAttributeRequest $request)
    {
        $hotelAttribute = $this->addHotelAttribute->handle($request);
        return $this->sendResponse($hotelAttribute->toArray(), 'create success', Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $hotelAttribute = HotelAttribute::findOrFail($id);
        return $this->sendResponse($hotelAttribute->toArray(), 'show success');
    }

    public function update(HotelAttributeRequest $request, $id)
    {
        $hotelAttribute = HotelAttribute::findOrFail($id);
        $hotelAttribute = $this->editHotelAttribute->handle($hotelAttribute, $request);
        return $this->sendResponse($hotelAttribute->toArray(), 'update success');
    }

    public function destroy($id)
    {
        $hotelAttribute = HotelAttribute::findOrFail($id);
        $this->deleteHotelAttribute->handle($hotelAttribute);
        return $this->sendResponse([], 'delete success', Response::HTTP_NO_CONTENT);
    }
}
