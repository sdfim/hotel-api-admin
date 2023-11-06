<?php

namespace Modules\API\BookingAPI;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface BookingApiHandlerInterface
{
    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addItem(Request $request, string $supplier): JsonResponse;

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function removeItem(Request $request, string $supplier): JsonResponse;

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function retrieveItems(Request $request, string $supplier): JsonResponse;

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function addPassengers(Request $request, string $supplier): JsonResponse;

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function book(Request $request, string $supplier): JsonResponse;

	/**
	 * @param Request $request
	 * @param string $supplier
	 * @return JsonResponse
	 */
	public function changeBooking(Request $request, string $supplier): JsonResponse;

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function listBookings(Request $request, string $supplier): JsonResponse;

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function retrieveBooking(Request $request, string $supplier): JsonResponse;

    /**
     * @param Request $request
     * @param string $supplier
     * @return JsonResponse
     */
    public function cancelBooking(Request $request, string $supplier): JsonResponse;
}
