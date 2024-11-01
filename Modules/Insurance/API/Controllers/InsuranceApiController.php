<?php

namespace Modules\Insurance\API\Controllers;

use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Http\JsonResponse;
use Modules\API\BaseController;
use Modules\Insurance\API\Requests\InsuaranceAddRequest;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRateTier;

class InsuranceApiController extends BaseController
{
    public function add(InsuaranceAddRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $insurancePlan = new InsurancePlan();
        $insurancePlan->booking_item = $validated['booking_item'];

        $isBooked = ApiBookingInspectorRepository::isBook($insurancePlan->booking_item, $validated['booking_item']);

        //TODO: find out if we need to applying pricing rules before calculating insurance price

        if (!$isBooked) return $this->sendError('Invalid booking item', 500);

        $bookingItemTotalPrice = 0;

        /*Separate Table with Frontend *:
        We could set up a separate table for the price ranges(X Price & Y Price) and the insurance markup, with a simple frontend to manage these ranges .
        This would make it easy to add or change them when needed .*/

        $insuranceRateTier = InsuranceRateTier::where('min_price', '<=', $bookingItemTotalPrice)
            ->where('max_price', '>=', $bookingItemTotalPrice)
            ->first();

        $insurancePlan->total_insurance_cost = ($bookingItemTotalPrice / $insuranceRateTier->insurance_rate) * 100;

//        $insurancePlan->supplier_fee = 0;
//        $insurancePlan->commission_ujv = 0;

        $insurancePlan->insurance_provider_id = InsuranceProvider::where('name', $validated['insurance_provider'])->pluck('id')->first();

        if ($insurancePlan->save()) {
            return $this->sendResponse($insurancePlan->toArray(), 'Insurance plan added successfully', 201);
        } else {
            return $this->sendError('Failed to add insurance plan', 500);
        }
    }
}
