<?php

namespace Modules\Insurance\API\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Models\Supplier;
use App\Repositories\ApiBookingInspectorRepository;
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

class InsuranceApiController extends BaseController
{
    public function add(InsuranceAddRequest $request): JsonResponse
    {
        // Define $bookingInspector before the try block to avoid undefined variable warning
        $bookingInspector = null;
        // Initialize $originalRQ as well to ensure it's accessible in catch
        $originalRQ = null;

        $bookingItem = $request->input('booking_item');

        // Check if the InsurancePlan with the same booking_item already exists
        $existingInsurancePlan = InsurancePlan::where('booking_item', $bookingItem)->first();

        if ($existingInsurancePlan) {
            // If the InsurancePlan already exists, return it with its applications
            $insurancePlanDTO = new InsurancePlanDTO($existingInsurancePlan);
            return $this->sendResponse($insurancePlanDTO->data, 'Insurance plan already exists with the specified booking item.', 201);
        }

        // Start a database transaction to ensure atomic operations
        DB::beginTransaction();

        try {
            // Create a new InsurancePlan
            $insurancePlan = new InsurancePlan();
            $insurancePlan->booking_item = $bookingItem;

            [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = $this->getParams($request);

            if (!$apiBookingInspectorItem) {
                return $this->sendError('The specified booking item is not valid or not found in the booking inspector', 404);
            }

            $insuranceProvider = InsuranceProvider::where('name', $request['insurance_provider'])->first();

            if (!$insuranceProvider) {
                return $this->sendError('The selected insurance provider is invalid or unavailable', 404);
            }

            $searchId = $apiBookingInspectorItem->search_id;

            $itemPricing = ApiBookingItemRepository::getItemPricingData($bookingItem);

            if (empty($itemPricing)) {
                return $this->sendError('Unable to retrieve pricing data for the specified booking item', 500);
            }

            $apiSearchInspectorItem = ApiSearchInspectorRepository::getRequest($searchId);

            $bookingItemTotalPrice = (float)Arr::get($itemPricing, 'total_price', 0);

            $insuranceRateTier = InsuranceRateTier::where('insurance_provider_id', $insuranceProvider->id)
                ->where('min_price', '<=', $bookingItemTotalPrice)
                ->where('max_price', '>=', $bookingItemTotalPrice)
                ->first();

            if (!$insuranceRateTier) {
                return $this->sendError('No applicable insurance rate tier found.', 400);
            }

            // Calculate the insurance provider fee
            $insuranceProviderFee = $insuranceProvider->rate_type === 'fixed' ?
                $insuranceProvider->rate_value : ($bookingItemTotalPrice / 100) * $insuranceRateTier->rate_value;

            $commissionUjv = (float)($bookingItemTotalPrice / 100) * (float)env('INSURANCE_COMMISSION', 5);

            $insurancePlan->total_insurance_cost = $insuranceProviderFee + $commissionUjv;
            $insurancePlan->insurance_provider_fee = $insuranceProviderFee;
            $insurancePlan->commission_ujv = $commissionUjv;
            $insurancePlan->insurance_provider_id = $insuranceProvider->id;
            $insurancePlan->request = $request->all();

            if (!$insurancePlan->save()) {
                DB::rollBack();
                return $this->sendError('Failed to create the insurance plan. Please try again later.', 500);
            }

            // Populate insurance applications
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

            $bookingInspector = BookingRepository::newBookingInspector([
                $bookingId, $filters, $supplierId, 'add_insurance', '', 'hotel',
            ]);

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

            return $this->sendError('An error occurred while processing the insurance plan.', 500);
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

        [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = $this->getParams($request);

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

    private function getParams($request): array
    {
        $bookingItem = $request->input('booking_item');
        $apiBookingInspectorItem = ApiBookingInspectorRepository::isBookingItemInCart($bookingItem);

        if (!$apiBookingInspectorItem) {
            return [];
        }

        $searchId = $apiBookingInspectorItem->search_id;
        $apiSearchInspectorItem = ApiSearchInspectorRepository::getRequest($searchId);

        $bookingId = $apiBookingInspectorItem->booking_id;

        $filters = $request->all();
        $filters['search_id'] = $apiBookingInspectorItem->search_id;

        $supplierId = Supplier::where('name', (string)$apiSearchInspectorItem['supplier'])->first()->id;

        return [$bookingId, $filters, $supplierId, $apiBookingInspectorItem];
    }
}
