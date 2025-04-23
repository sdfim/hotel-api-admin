<?php

namespace Modules\Addons\API\Controllers;

use App\Models\ApiBookingItem;
use Modules\Addons\API\Requests\InformativeServiceRequest;
use Modules\HotelContentRepository\API\Requests\InformativeServiceRetrieveRequest;
use Modules\HotelContentRepository\API\Controllers\BaseController;
use Modules\HotelContentRepository\Services\InformativeServiceService;

class InformativeServiceController extends BaseController
{
    public function __construct(protected InformativeServiceService $informativeServiceService) {}

    public function attach(InformativeServiceRequest $request)
    {
        $servicesForAttach = $this->informativeServiceService->getServicesForAttach($request);
        $bookingItem = ApiBookingItem::find($request->input('booking_item'));
        $responseServices = $this->informativeServiceService->getResponseServices($bookingItem, $servicesForAttach, 'attach');
        $servicesToAttach = $this->informativeServiceService->getServicesToAttach($bookingItem, $servicesForAttach);

        if (empty($servicesToAttach)) {
            return $this->sendResponse($responseServices, 'All services are already attached', 200);
        }

        $attachData = [];
        foreach ($servicesToAttach as $k => $serviceId) {
            $rsServices = $request->input('services');
            $cost = $rsServices[$k]['cost'];
            $attachData[$serviceId] = [
                'cost' => $cost,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $bookingItem->informationalServices()->attach($attachData);
        $this->informativeServiceService->recordBookingInspector($request, $responseServices, 'service_attach');

        return $this->sendResponse($responseServices, 'Services attached successfully', 201);
    }

    public function detach(InformativeServiceRequest $request)
    {
        $servicesForDetach = $this->informativeServiceService->getServicesForDetach($request);
        $bookingItem = ApiBookingItem::find($request->input('booking_item'));
        $responseServices = $this->informativeServiceService->getResponseServices($bookingItem, $servicesForDetach, 'detach');
        $servicesToDetach = $this->informativeServiceService->getServicesToDetach($bookingItem, $servicesForDetach);

        if (empty($servicesToDetach)) {
            return $this->sendResponse($responseServices, 'All services are already detached', 200);
        }

        $bookingItem->informationalServices()->detach($servicesToDetach);
        $this->informativeServiceService->recordBookingInspector($request, $responseServices, 'service_detach');

        return $this->sendResponse($responseServices, 'Services detached successfully', 200);
    }

    public function retrieve(InformativeServiceRetrieveRequest $request)
    {
        $bookingItem = ApiBookingItem::find($request->input('booking_item'));
        $attachedServices = $bookingItem->informationalServices->map(function ($service) {
            return [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'cost' => $service->pivot->cost,
            ];
        })->toArray();

        return $this->sendResponse($attachedServices, 'Services retrieved successfully', 200);
    }
}
