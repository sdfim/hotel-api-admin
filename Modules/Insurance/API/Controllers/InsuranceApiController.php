<?php

namespace Modules\Insurance\API\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\API\BaseController;
use Modules\Insurance\API\Requests\InsuranceAddRequest;
use Modules\Insurance\Models\DTOs\InsurancePlanDTO;
use Modules\Insurance\Models\InsuranceApplication;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRateTier;
use Modules\Insurance\Models\InsuranceRestriction;

class InsuranceApiController extends BaseController
{
    public function add(InsuranceAddRequest $request): JsonResponse
    {
        $bookingInspector = null;
        $originalRQ = null;
        $bookingItem = $request->input('booking_item');

        $insuranceProvider = InsuranceProvider::where('name', $request['insurance_provider'])->first();

        if (!$insuranceProvider) {
            return $this->sendError('The selected insurance provider is invalid or unavailable', 404);
        }

        // Validate the booking item
        $notValid = $this->validateBookingItem($bookingItem, $insuranceProvider->id);
        if (!empty($notValid)) {
            $errData = [
                'message' => 'The booking item does not meet the required conditions.',
                'condition' => $notValid,
            ];
            return $this->sendError('The booking item does not meet the required conditions.' , '', 400, $errData);
        }

        [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = BookingRepository::getParams($request);
        if (empty($bookingId) || empty($filters) || empty($supplierId) || empty($apiBookingInspectorItem)) {
            return $this->sendError('The specified booking item is not valid or not found in the booking inspector', 404);
        }

        $searchId = $apiBookingInspectorItem->search_id;

        $itemPricing = ApiBookingItemRepository::getItemPricingData($bookingItem);
        if (empty($itemPricing)) {
            return $this->sendError('Unable to retrieve pricing data for the specified booking item', 500);
        }

        $apiSearchInspectorItem = ApiSearchInspectorRepository::getRequest($searchId);

        $existingInsurancePlan = InsurancePlan::where('booking_item', $bookingItem)->first();

        if ($existingInsurancePlan) {
            $insurancePlanDTO = new InsurancePlanDTO($existingInsurancePlan);
            return $this->sendResponse($insurancePlanDTO->data, 'Insurance plan already exists with the specified booking item.', 201);
        }

        $bookingInspector = BookingRepository::newBookingInspector([
            $bookingId, $filters, $supplierId, 'add_insurance', '', 'hotel',
        ]);

        DB::beginTransaction();

        try {
            $insurancePlan = new InsurancePlan();
            $insurancePlan->booking_item = $bookingItem;

            // Determine the number of passengers
            $totalPassengersNumber = ApiSearchInspectorRepository::getTotalOccupancy($apiSearchInspectorItem['occupancy']);

            // Determine the cost per passenger
            $bookingItemTotalPrice = (float)Arr::get($itemPricing, 'total_price', 0);
            $costPerPassenger = $totalPassengersNumber > 0 ? $bookingItemTotalPrice / $totalPassengersNumber : 0;

            // Select the appropriate InsuranceRateTier
            $insuranceRateTier = InsuranceRateTier::where('insurance_provider_id', $insuranceProvider->id)
                ->where('min_trip_cost', '<=', $costPerPassenger)
                ->where('max_trip_cost', '>=', $costPerPassenger)
                ->first();

            if (!$insuranceRateTier) {
                return $this->sendError('No applicable insurance rate tier found.', 400);
            }

            // Calculate cost
            $totalPlanCost = $insuranceRateTier->net_to_trip_mate * $totalPassengersNumber;
            $commissionUjv = $insuranceRateTier->uiv_retention * $totalPassengersNumber;
            $insuranceProviderFee = $insuranceRateTier->consumer_plan_cost * $totalPassengersNumber;

            $insurancePlan->total_insurance_cost = $totalPlanCost;
            $insurancePlan->insurance_provider_fee = $insuranceProviderFee;
            $insurancePlan->commission_ujv = $commissionUjv;
            $insurancePlan->insurance_provider_id = $insuranceProvider->id;
            $insurancePlan->request = $request->all();

            if (!$insurancePlan->save()) {
                DB::rollBack();
                return $this->sendError('Failed to create the insurance plan. Please try again later.', 500);
            }

            // Populate insurance applications
            $totalInsuranceCostPerPerson = $totalPassengersNumber > 0 ? $insurancePlan->total_insurance_cost / $totalPassengersNumber : 0;

            $insuranceApplications = [];
            $now = now();

            foreach ($apiSearchInspectorItem['occupancy'] as $roomIndex => $room) {
                $baseApplicationData = [
                    'insurance_plan_id' => $insurancePlan->id,
                    'room_number' => $roomIndex + 1,
                    'name' => '',
                    'location' => $apiSearchInspectorItem['destination'] ?? '',
                    'total_insurance_cost_pp' => $totalInsuranceCostPerPerson,
                    'created_at' => $now,
                    'updated_at' => $now,
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

            // Insert the insurance applications into the database
            if (!InsuranceApplication::insert($insuranceApplications)) {
                DB::rollBack();
                return $this->sendError('Failed to create insurance applications. Please try again later.', 500);
            }

            // Prepare request and response data for logging
            $originalRQ = [
                'path' => $request->path(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ];

            // Return the newly created insurance plan with its applications
            $insurancePlanDTO = new InsurancePlanDTO($insurancePlan);
            $responseData = $insurancePlanDTO->data;
            $content['original']['request'] = $originalRQ;

            // Dispatch the SaveBookingInspector job
            SaveBookingInspector::dispatch($bookingInspector, $content, $responseData);

            // Commit the transaction
            DB::commit();

            // Return success response
            return $this->sendResponse($responseData, 'Insurance plan and related applications successfully created', 201);
        } catch (Exception $e) {
            // Rollback in case of error
            DB::rollBack();

            $message = "An error occurred while creating the insurance plan: {$e->getMessage()}";
            Log::error($message);
            Log::error($e->getTraceAsString());

            // Ensure $bookingInspector is defined, even in case of error
            $content = [];

            if ($bookingInspector !== null) {
                $content['original']['response'] = $bookingInspector;
            }
            if ($originalRQ !== null) {
                $content['original']['request'] = $originalRQ;
            }

            SaveBookingInspector::dispatch($bookingInspector, $content, [], 'error', ['side' => 'supplier', 'message' => $message]);

            return $this->sendError('An error occurred while processing the insurance plan.', '', 500);
        }
    }

    public function delete(InsuranceAddRequest $request): JsonResponse
    {
        $bookingItem = $request->input('booking_item');
        $insurancePlan = InsurancePlan::where('booking_item', $bookingItem)->first();

        if (!$insurancePlan) {
            return $this->sendError('Insurance plan not found', 404);
        }

        $insurancePlan->applications()->delete();
        $insurancePlan->delete();

        [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = BookingRepository::getParams($request);

        if (!$apiBookingInspectorItem) {
            return $this->sendError('The specified booking item is not valid or not found in the booking inspector', 404);
        }

        $bookingInspector = BookingRepository::newBookingInspector([
            $bookingId, $filters, $supplierId, 'delete_insurance', '', 'hotel',
        ]);

        $originalRQ = [
            'path' => $request->path(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ];

        $message = 'Insurance plan successfully deleted, no body 204 response';
        $responseData = ['message' => $message];
        SaveBookingInspector::dispatch($bookingInspector, ['original' => ['request' => $originalRQ]], $responseData);

        return $this->sendResponse([], $message,  204);
    }

    private function validateBookingItem($bookingItem, $providerId): array
    {
        $validationRules = InsuranceRestriction::with('restrictionType')
            ->where('provider_id', $providerId)
            ->get()
            ->map(function ($restriction) {
                return [
                    'provider' => $restriction->provider->name,
                    'restriction_type' => $restriction->restrictionType->name,
                    'compare_sign' => $restriction->compare,
                    'restriction_value' => $restriction->value,
                ];
            });

        $log = [];
        foreach ($validationRules as $rule) {
            $type = $rule['restriction_type'];
            if ($type === 'customer_location'
                || $type === 'insurance_return_period_days')
            {
                continue;
            }
            $restrictionType = $type;
            $compareSign = $rule['compare_sign'];
            $restrictionValue = $rule['restriction_value'];

            // Retrieve the actual value from the booking item
            $actualValue = $this->getBookingItemValue($bookingItem, $restrictionType);

            $comparisonResult = $this->compareValues($actualValue, $compareSign, $restrictionValue);
            $log[] = [
                'restrictionType' => $restrictionType,
                'actualValue' => $actualValue,
                'compareSign' => $compareSign,
                'restrictionValue' => $restrictionValue,
                'result' => $comparisonResult,
            ];

            if (!$comparisonResult) {
                return $rule;
            }
        }

        Log::info('Comparison result', $log);

        return [];
    }

    private function getBookingItemValue(string $bookingItem, string $restrictionType)
    {
        $bookingItemModel = ApiBookingItem::where('booking_item', $bookingItem)->first();
        $search = ApiSearchInspectorRepository::getRequest($bookingItemModel->search_id);
        if ($restrictionType === 'age') {
            $occupancy = Arr::get($search, 'occupancy');
            $ages = collect($occupancy)->flatMap(function ($room) {
                return Arr::get($room, 'children_ages', []);
            });
            return $ages->isNotEmpty() ? $ages->min() : 33;
        }
        if ($restrictionType === 'travel_location') {
            return Arr::get($search, 'destination');
        }
        if ($restrictionType === 'trip_duration_days') {
            $checkIn = Arr::get($search, 'check_in');
            $checkOut = Arr::get($search, 'check_out');
            return $checkIn && $checkOut ? $checkIn->diffInDays($checkOut) : null;
        }
        if ($restrictionType === 'trip_cost') {
            $pricingData = ApiBookingItemRepository::getItemPricingData($bookingItem);
            return Arr::get($pricingData, 'total_price');
        }
        return null;
    }

    private function compareValues(mixed $actualValue, string $compareSign, mixed $restrictionValue): bool
    {
        switch ($compareSign) {
            case '<':
                return $actualValue < $restrictionValue;
            case '>':
                return $actualValue > $restrictionValue;
            case '=':
                return $actualValue == $restrictionValue;
            case '!=':
                return $actualValue != $restrictionValue;
            default:
                return false;
        }
    }
}
