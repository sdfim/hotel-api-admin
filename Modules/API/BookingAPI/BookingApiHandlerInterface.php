<?php

namespace Modules\API\BookingAPI;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

interface BookingApiHandlerInterface
{
	public function addItem(Request $request, string $supplier) : JsonResponse;

	public function removeItem(Request $request, string $supplier) : JsonResponse;

	public function retrieveItems(Request $request, string $supplier) : JsonResponse;

	public function addPassengers(Request $request, string $supplier) : JsonResponse;

	public function book(Request $request, string $supplier) : JsonResponse;

	public function listBookings(Request $request, string $supplier) : JsonResponse;

	public function retrieveBooking(Request $request, string $supplier) : JsonResponse;
	
	public function cancelBooking(Request $request, string $supplier) : JsonResponse;
}