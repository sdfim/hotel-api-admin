<?php

namespace Modules\Insurance\API\Controllers;

use App\Jobs\SaveBookingInspector;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\API\BaseController;
use Modules\Insurance\API\InsuranceHelper;
use Modules\Insurance\API\Requests\InsuranceAddRequest;
use Modules\Insurance\Models\DTOs\InsurancePlanDTO;
use Modules\Insurance\Models\InsuranceApplication;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRateTier;
use Modules\Insurance\Models\InsuranceRestriction;

class InsuranceApiController extends BaseController
{
    use InsuranceHelper;

    public function add(InsuranceAddRequest $request): JsonResponse
    {
        $originalRQ = null;
        $bookingId = $request->input('booking_id');
        $bookingItem = $request->input('booking_item');

        $insuranceProvider = $this->getInsuranceProvider($request['insurance_provider']);
        if (!$insuranceProvider) {
            return $this->sendError('The selected insurance provider is invalid or unavailable', 404);
        }

        $bookingItems = $this->getBookingItems($bookingId, $bookingItem);
        if (empty($bookingItems)) {
            return $this->sendError('Either booking_id or booking_item must be provided', 400);
        }

        $responceAll = [];
        $errCount = 0;

        foreach ($bookingItems as $bookingItem) {
            $notValid = $this->validateBookingItem($bookingItem, $insuranceProvider->id);
            if (!empty($notValid)) {
                return $this->sendError('The booking item does not meet the required conditions.', '', 400, [
                    'message' => 'The booking item does not meet the required conditions.',
                    'condition' => $notValid,
                ]);
            }

            [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = BookingRepository::getParams($request, $bookingItem);
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
                unset($insurancePlanDTO->data['request']);
                $responceAll[] = array_merge($insurancePlanDTO->data, ['message' => 'Insurance plan already exists with the specified booking item.']);
                continue;
            }

            $filters['booking_item'] = $bookingItem;
            $bookingInspector = BookingRepository::newBookingInspector([
                $bookingId, $filters, $supplierId, 'add_insurance', '', 'hotel',
            ]);

            DB::beginTransaction();

            try {
                $insurancePlan = $this->createInsurancePlan($request, $bookingItem, $insuranceProvider, $itemPricing, $apiSearchInspectorItem);
                $insuranceApplications = $this->createInsuranceApplications($insurancePlan, $apiSearchInspectorItem);

                if (!InsuranceApplication::insert($insuranceApplications)) {
                    DB::rollBack();
                    return $this->sendError('Failed to create insurance applications. Please try again later.', 500);
                }

                $insurancePlanDTO = new InsurancePlanDTO($insurancePlan);
                $responseData = $insurancePlanDTO->data;

                $this->dispatchSaveBookingInspector($request, $bookingInspector, $responseData);

                DB::commit();

                unset($responseData['request']);
                $responceAll[] = array_merge($responseData, ['message' => 'Insurance plan and related applications successfully created']);
            } catch (Exception $e) {
                DB::rollBack();
                $this->handleException($e, $bookingInspector, $originalRQ, $responceAll, $bookingItem);
                $errCount++;
            }
        }

        return $this->finalizeResponse($errCount, $responceAll, $request);
    }

    public function delete(InsuranceAddRequest $request): JsonResponse
    {
        $bookingId = $request->input('booking_id');
        $bookingItem = $request->input('booking_item');

        $bookingItems = $this->getBookingItems($bookingId, $bookingItem);
        if (empty($bookingItems)) {
            return $this->sendError('Either booking_id or booking_item must be provided', 400);
        }

        foreach ($bookingItems as $bookingItem) {
            $insurancePlan = InsurancePlan::where('booking_item', $bookingItem)->first();

            if (!$insurancePlan) {
                return $this->sendError('Insurance plan not found for booking item: ' . $bookingItem, 404);
            }

            $insurancePlan->applications()->delete();
            $insurancePlan->delete();

            [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = BookingRepository::getParams($request, $bookingItem);

            if (!$apiBookingInspectorItem) {
                return $this->sendError('The specified booking item is not valid or not found in the booking inspector', 404);
            }

            $filters['booking_item'] = $bookingItem;
            $bookingInspector = BookingRepository::newBookingInspector([
                $bookingId, $filters, $supplierId, 'delete_insurance', '', 'hotel',
            ]);

            $originalRQ = [
                'path' => $request->path(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ];

            $message = 'Insurance plan successfully deleted for booking item: ' . $bookingItem;
            $responseData = ['message' => $message];
            SaveBookingInspector::dispatch($bookingInspector, ['original' => ['request' => $originalRQ]], $responseData);
        }

        return $this->sendResponse([], 'Insurance plans successfully deleted', 204);
    }

    public function retrieve(InsuranceAddRequest $request): JsonResponse
    {
        $bookingId = $request->input('booking_id');
        $bookingItem = $request->input('booking_item');

        if ($bookingId) {
            $bookingItems = $this->getBookingItems($bookingId, null);
            $insurancePlans = InsurancePlan::whereIn('booking_item', $bookingItems)->get();
        } elseif ($bookingItem) {
            $insurancePlans = InsurancePlan::where('booking_item', $bookingItem)->get();
        } else {
            $insurancePlans = InsurancePlan::all();
        }

        if ($insurancePlans->isEmpty()) {
            return $this->sendError('No insurance plans found', 404);
        }

        $insurancePlansDTO = $insurancePlans->map(function ($insurancePlan) {
            return (new InsurancePlanDTO($insurancePlan))->data;
        });

        return $this->sendResponse($insurancePlansDTO->toArray(), 'Insurance plans retrieved successfully', 200);
    }
}
