<?php

namespace Modules\API\BookingAPI\BookingApiHandlers;

use Modules\API\BaseController;
use Modules\API\BookingAPI\BookingApiHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FlightBookingApiHandler extends BaseController implements BookingApiHandlerInterface
{
    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addItem(Request $request, string $supplier): JsonResponse
    {
        return $this->sendResponse(['result' => 'addItem'], 'success');
    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function removeItem(Request $request, string $supplier): JsonResponse
    {

    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function retrieveItems(Request $request, string $supplier): JsonResponse
    {

    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addPassengers(Request $request, string $supplier): JsonResponse
    {

    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function book(Request $request, string $supplier): JsonResponse
    {

    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function listBookings(Request $request, string $supplier): JsonResponse
    {

    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function retrieveBooking(Request $request, string $supplier): JsonResponse
    {

    }

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function cancelBooking(Request $request, string $supplier): JsonResponse
    {

    }
}
