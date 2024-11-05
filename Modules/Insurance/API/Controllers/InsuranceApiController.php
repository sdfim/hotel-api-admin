<?php

namespace Modules\Insurance\API\Controllers;

use App\Repositories\ApiBookingInspectorRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Illuminate\Http\JsonResponse;
use Modules\API\BaseController;
use Modules\Insurance\API\Requests\InsuaranceAddRequest;
use Modules\Insurance\Models\InsuranceApplication;
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

        // Check if the booking_item is in cart from app booking inspector
        $apiBookingInspectorItem = ApiBookingInspectorRepository::isBookingItemInCart($validated['booking_item']);

        if (!$apiBookingInspectorItem) {
            return $this->sendError('The specified booking item is not valid or not found in the booking inspector', 404);
        }

        $insuranceProvider = InsuranceProvider::where('name', $validated['provider'])->first();

        if (!$insuranceProvider) {
            return $this->sendError('The selected insurance provider is invalid or unavailable', 404);
        }

        $searchId = $apiBookingInspectorItem->search_id;

        $itemPricing = ApiBookingItemRepository::getItemPricingData($validated['booking_item']);

        if (!$itemPricing) {
            return $this->sendError('Unable to retrieve pricing data for the specified booking item', 500);
        }

        $apiSearchInspectorItem = ApiSearchInspectorRepository::getRequest($searchId);

        $bookingItemTotalPrice = $itemPricing['total_price'] ?? 0;

        $insuranceRateTier = InsuranceRateTier::where('insurance_provider_id', $insuranceProvider->id)
            ->where('min_price', '<=', $bookingItemTotalPrice)
            ->where('max_price', '>=', $bookingItemTotalPrice)
            ->first();

        if (!$insuranceRateTier) {
            return $this->sendError('No applicable insurance rate tier found.', 400);
        }

        $insuranceProviderFee = $insuranceProvider->rate_type === 'fixed' ?
            $insuranceProvider->rate_value : ($bookingItemTotalPrice / 100) * $insuranceRateTier->rate_value;

        $commissionUjv = ($bookingItemTotalPrice / 100) * env('UJV_INSURANCE_COMMISSION', 5);

        $insurancePlan->total_insurance_cost = $insuranceProviderFee + $commissionUjv;
        $insurancePlan->insurance_provider_fee = $insuranceProviderFee;
        $insurancePlan->commission_ujv = $commissionUjv;
        $insurancePlan->insurance_provider_id = $insuranceProvider->id;

        if (!$insurancePlan->save()) {
            return $this->sendError('Failed to create the insurance plan. Please try again later.', 500);
        }

        $totalPassengersNumber = ApiSearchInspectorRepository::getTotalOccupancy($apiSearchInspectorItem['occupancy']);
        $totalInsuranceCostPerPerson = $totalPassengersNumber > 0 ? $insurancePlan->total_insurance_cost / $totalPassengersNumber : 0;

        $insuranceApplications = [];
        $now = now();

        foreach ($apiSearchInspectorItem['occupancy'] as $roomIndex => $room) {
            $baseApplicationData = [
                'insurance_plan_id' => $insurancePlan->id,
                'room_number' => $roomIndex + 1,
                'name' => '',
                'location' => $apiSearchInspectorItem['destination'] ?? '',
                'applied_at' => $now,
                'total_insurance_cost_pp' => $totalInsuranceCostPerPerson,
            ];

            if (!empty($room['children_ages'])) {
                foreach ($room['children_ages'] as $childrenAge) {
                    $insuranceApplications[] = $baseApplicationData + ['age' => $childrenAge];
                }
            }

            for ($i = 0; $i < $room['adults']; $i++) {
                $insuranceApplications[] = $baseApplicationData + ['age' => 33];
            }
        }

        if (InsuranceApplication::insert($insuranceApplications)) {
            return $this->sendResponse($insurancePlan->toArray(), 'Insurance plan and related applications successfully created', 201);
        } else {
            return $this->sendError('Failed to create insurance applications. Please try again later.', 500);
        }
    }
}
