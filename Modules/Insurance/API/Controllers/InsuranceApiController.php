<?php

namespace Modules\Insurance\API\Controllers;

use App\Models\ApiBookingInspector;
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
    private int $ujvCommission = 5;

    public function add(InsuaranceAddRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $insurancePlan = new InsurancePlan();
        $insurancePlan->booking_item = $validated['booking_item'];

        // Check if the booking_item is in cart from app booking inspector
        $apiBookingInspectorItem = ApiBookingInspector::where('type', 'add_item')
            ->where('booking_item', $validated['booking_item'])
            ->where('status', 'success')
            ->first();

        if (!$apiBookingInspectorItem) return $this->sendError('Invalid booking item', 500);

        $searchId = $apiBookingInspectorItem->search_id;

        $itemPricing = ApiBookingItemRepository::getItemPricingData($validated['booking_item']);

        // go api search inspector, check json of request, get number of passengers, children and their ages
        $apiSearchInspectorItem = ApiSearchInspectorRepository::getRequest($searchId);

        $insuranceApplications = [];

        // TODO: loop through occupancy to create insurance application records, we can get children ages from request, as well as location(for now we should use destination key, determinate by coordinates will be implemented later)

        InsuranceApplication::create($insuranceApplications);

        $insuranceProvider = InsuranceProvider::where('name', $validated['provider'])->first();

        if (!$insuranceProvider) return $this->sendError('Invalid insurance provider', 500);

        $bookingItemTotalPrice = 0;

        $insuranceRateTier = InsuranceRateTier::where('insurance_provider_id', $insuranceProvider->id)
            ->where('min_price', '<=', $bookingItemTotalPrice)
            ->where('max_price', '>=', $bookingItemTotalPrice)
            ->first();

        $insuranceProviderFee = $insuranceProvider->rate_type === 'fixed' ?
            $insuranceProvider->rate_value : ($bookingItemTotalPrice / 100) * $insuranceRateTier->rate_value;

        $commissionUjv = ($bookingItemTotalPrice / 100) * $this->ujvCommission;

        $insurancePlan->total_insurance_cost = $insuranceProviderFee + $commissionUjv;

        $insurancePlan->insurance_provider_fee = $insuranceProviderFee;

        $insurancePlan->commission_ujv = $commissionUjv;

        $insurancePlan->insurance_provider_id = $insuranceProvider->id;

        if ($insurancePlan->save()) {
            return $this->sendResponse($insurancePlan->toArray(), 'Insurance plan added successfully', 201);
        } else {
            return $this->sendError('Failed to add insurance plan', 500);
        }
    }
}
