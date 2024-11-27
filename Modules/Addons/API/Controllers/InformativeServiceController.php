<?php

namespace Modules\Addons\API\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingItem;
use App\Models\Configurations\ConfigServiceType;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use Modules\Addons\API\Requests\InformativeServiceRequest;
use Modules\HotelContentRepository\API\Requests\InformativeServiceRetrieveRequest;
use Modules\HotelContentRepository\API\Controllers\BaseController;

class InformativeServiceController extends BaseController
{
    public function attach(InformativeServiceRequest $request)
    {
        $servicesForAttach = $this->getServicesForAttach($request);
        if (empty($servicesForAttach)) {
            return $this->sendError('No valid services found', 404);
        }

        $bookingItem = ApiBookingItem::find($request->input('booking_item'));
        $responseServices = $this->getResponseServices($bookingItem, $servicesForAttach, 'attach');
        $servicesToAttach = $this->getServicesToAttach($bookingItem, $servicesForAttach);

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
        $this->recordBookingInspector($request, $responseServices, 'service_attach');

        return $this->sendResponse($responseServices, 'Services attached successfully', 201);
    }

    public function detach(InformativeServiceRequest $request)
    {
        $servicesForDetach = $this->getServicesForDetach($request);
        if (empty($servicesForDetach)) {
            return $this->sendError('No valid services found', 404);
        }

        $bookingItem = ApiBookingItem::find($request->input('booking_item'));
        $responseServices = $this->getResponseServices($bookingItem, $servicesForDetach, 'detach');
        $servicesToDetach = $this->getServicesToDetach($bookingItem, $servicesForDetach);

        if (empty($servicesToDetach)) {
            return $this->sendResponse($responseServices, 'All services are already detached', 200);
        }

        $bookingItem->informationalServices()->detach($servicesToDetach);
        $this->recordBookingInspector($request, $responseServices, 'service_detach');

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


    private function getServices($request)
    {
        $services = [];
        foreach ($request->input('services') as $service) {
            $serviceTypes = $this->findServiceType($service);
            if ($serviceTypes) {
                $services[] = $serviceTypes->id;
            }
        }
        return $services;
    }

    public function getServicesForAttach($request)
    {
        return $this->getServices($request);
    }

    public function getServicesForDetach($request)
    {
        return $this->getServices($request);
    }

    private function getServicesToDetach($bookingItem, $servicesForDetach)
    {
        $attachedServices = $bookingItem->informationalServices()->whereIn('service_id', $servicesForDetach)->pluck('service_id')->toArray();
        return array_intersect($servicesForDetach, $attachedServices);
    }

    private function findServiceType($service)
    {
        if (isset($service['service_id'])) {
            return ConfigServiceType::find($service['service_id']);
        } elseif (isset($service['service_name'])) {
            return ConfigServiceType::where('name', $service['service_name'])->first();
        }
        return null;
    }

    private function getResponseServices($bookingItem, $servicesForAttach, $actionType)
    {
        $responseServices = [];
        $alreadyAttachedServices = $bookingItem->informationalServices()->whereIn('service_id', $servicesForAttach)->pluck('service_id')->toArray();

        foreach ($servicesForAttach as $k => $serviceId) {
            $service = ConfigServiceType::find($serviceId);
            $message = '';

            if ($actionType === 'attach') {
                $message = in_array($serviceId, $alreadyAttachedServices) ? 'The service is already attached' : 'The service is attached';
                $rqServices = request()->input('services');
                $cost = $rqServices[$k]['cost'];
            } elseif ($actionType === 'detach') {
                $message = in_array($serviceId, $alreadyAttachedServices) ? 'The service is detached' : 'The service is not attached';
                $cost = null;
            }

            $responseService = [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'message' => $message,
            ];

            if ($cost !== null) {
                $responseService['cost'] = $cost;
            }

            $responseServices[] = $responseService;
        }

        return array_intersect_key($responseServices, array_unique(array_column($responseServices, 'service_id')));
    }
    private function getServicesToAttach($bookingItem, $servicesForAttach)
    {
        $alreadyAttachedServices = $bookingItem->informationalServices()->whereIn('service_id', $servicesForAttach)->pluck('service_id')->toArray();
        return array_diff($servicesForAttach, $alreadyAttachedServices);
    }

    private function recordBookingInspector($request, $responseServices, $actionType)
    {
        [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = BookingRepository::getParams($request);

        if (!$apiBookingInspectorItem) {
            return $this->sendError('The specified booking item is not valid or not found in the booking inspector', 404);
        }

        $filters['booking_id'] = $bookingId;

        $dataRQ['original']['request'] = [
            'path' => $request->path(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ];

        $dataRS = [
            'type' => 'informational_services',
            'attributes' => $responseServices,
        ];

        $bookingInspector = BookingRepository::newBookingInspector([
            $bookingId, $filters, $supplierId, $actionType, '', 'hotel',
        ]);

        SaveBookingInspector::dispatch($bookingInspector, $dataRQ, $dataRS);
    }
}
