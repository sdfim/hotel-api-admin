<?php

namespace Modules\HotelContentRepository\API\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\Configurations\ConfigServiceType;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Http\Response;
use Modules\HotelContentRepository\API\Requests\InformativeServiceRequest;
use Modules\HotelContentRepository\Models\HotelInformativeService;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class InformativeServiceController extends BaseController
{
    public function attach(InformativeServiceRequest $request)
    {
        $configServiceType = null;

        if ($request->has('service_id')) {
            $configServiceType = ConfigServiceType::find($request->input('service_id'));
        } elseif ($request->has('service_name')) {
            $configServiceType = ConfigServiceType::where('name', $request->input('service_name'))->first();
        }

        if (!$configServiceType) {
            return $this->sendError('The specified service is not valid or not found', 404);
        }

        [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = $this->getParams($request);
        $bookingInspector = BookingRepository::newBookingInspector([
            $bookingId, $filters, $supplierId, 'attach_service', '', 'hotel',
        ]);

        if (!$apiBookingInspectorItem) {
            return $this->sendError('The specified booking item is not valid or not found in the booking inspector', 404);
        }

        $filters['booking_id'] = $bookingId;

        $request['original']['request'] = [
            'path' => $request->path(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ];

        $response = [
            'type' => 'hotel_informative_services',
            'attributes' => $request->all(),
        ];

        SaveBookingInspector::dispatch($bookingInspector, $request, $response);

        return $this->sendResponse($response, 'attach success', 201);
    }

    public function detach(InformativeServiceRequest $request)
    {
        $hotelInformativeService = HotelInformativeService::create($request->validated());
        return $this->sendResponse($hotelInformativeService->toArray(), 'create success', 204);
    }

    private function getParams($request): array
    {
        $bookingItem = $request->input('booking_item');
        $apiBookingInspectorItem = ApiBookingInspectorRepository::isBookingItemInCart($bookingItem);

        if (!$apiBookingInspectorItem) {
            return [];
        }

        $searchId = $apiBookingInspectorItem->search_id;
        $apiSearchInspectorItem = ApiSearchInspectorRepository::getRequest($searchId);

        $bookingId = $apiBookingInspectorItem->booking_id;

        $filters = $request->all();
        $filters['search_id'] = $apiBookingInspectorItem->search_id;

        $supplierId = Supplier::where('name', (string)$apiSearchInspectorItem['supplier'])->first()->id;

        return [$bookingId, $filters, $supplierId, $apiBookingInspectorItem];
    }

}
