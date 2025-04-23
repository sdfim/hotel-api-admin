<?php

namespace Modules\HotelContentRepository\Services;

use App\Models\Configurations\ConfigServiceType;

class InformativeServiceService
{
    public function getServices($request)
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

    public function getServicesToDetach($bookingItem, $servicesForDetach)
    {
        $attachedServices = $bookingItem->informationalServices()->whereIn('service_id', $servicesForDetach)->pluck('service_id')->toArray();

        return array_intersect($servicesForDetach, $attachedServices);
    }

    public function findServiceType($service)
    {
        if (isset($service['service_id'])) {
            return ConfigServiceType::find($service['service_id']);
        } elseif (isset($service['service_name'])) {
            return ConfigServiceType::where('name', $service['service_name'])->first();
        }

        return null;
    }

    public function getResponseServices($bookingItem, $servicesForAttach, $actionType)
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

    public function getServicesToAttach($bookingItem, $servicesForAttach)
    {
        $alreadyAttachedServices = $bookingItem->informationalServices()->whereIn('service_id', $servicesForAttach)->pluck('service_id')->toArray();

        return array_diff($servicesForAttach, $alreadyAttachedServices);
    }

    public function recordBookingInspector($request, $responseServices, $actionType)
    {
        [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = BookingRepository::getParams($request);

        if (! $apiBookingInspectorItem) {
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
