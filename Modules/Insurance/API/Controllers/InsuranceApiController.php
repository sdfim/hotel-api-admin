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
use Modules\HotelContentRepository\Actions\Insurance\AddInsuranceApplication;
use Modules\HotelContentRepository\Actions\Insurance\DeleteInsurance;
use Modules\HotelContentRepository\Services\Suppliers\InsuranceService;
use Modules\Insurance\API\Requests\InsuranceAddRequest;
use Modules\Insurance\Models\DTOs\InsurancePlanDTO;
use Modules\Insurance\Models\InsurancePlan;
use Throwable;

class InsuranceApiController extends BaseController
{
    public function __construct(
        protected InsuranceService $insuranceService,
        protected InsurancePlanDTO $insurancePlanDTO,
        private readonly DeleteInsurance $deleteInsurance,
        private readonly AddInsuranceApplication $addInsuranceApplication,
    ) {}

    public function add(InsuranceAddRequest $request): JsonResponse
    {
        $originalRQ = null;
        $bookingId = $request->input('booking_id');
        $bookingItem = $request->input('booking_item');

        $insuranceProvider = $this->insuranceService->getInsuranceProvider($request['vendor']);
        if (! $insuranceProvider) {
            return $this->sendError('The selected insurance vendor is invalid or unavailable', 404);
        }

        $insuranceType = $this->insuranceService->getInsuranceType($request['insurance_type']);
        if (! $insuranceType) {
            return $this->sendError('The selected insurance insurance_type is invalid or unavailable', 404);
        }

        $bookingItems = $this->insuranceService->getBookingItems($bookingId, $bookingItem);
        if (empty($bookingItems)) {
            return $this->sendError('Either booking_id or booking_item must be provided', 400);
        }

        $responseAll = [];
        $errCount = 0;

        foreach ($bookingItems as $bookingItem) {
            // check if $bookingItem corresponds to restrictions
            $notValid = $this->insuranceService->validateBookingItem($bookingItem, $insuranceProvider->id, $insuranceType->id);
            if (! empty($notValid)) {
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
                $insurancePlanDTO = $this->insurancePlanDTO->setData($existingInsurancePlan);
                unset($insurancePlanDTO['request']);
                $responseAll[] = array_merge($insurancePlanDTO, ['message' => 'Insurance plan already exists with the specified booking item.']);

                continue;
            }

            $filters['booking_item'] = $bookingItem;
            $bookingInspector = BookingRepository::newBookingInspector([
                $bookingId, $filters, $supplierId, 'add_insurance', '', 'hotel',
            ]);

            DB::transaction(function () use ($request, $bookingItem, $insuranceProvider, $insuranceType, $itemPricing, $apiSearchInspectorItem, &$responseAll, &$errCount, $bookingInspector, &$originalRQ) {
                try {
                    $insurancePlan = $this->insuranceService->createInsurancePlan($request, $bookingItem, $insuranceProvider, $insuranceType, $itemPricing, $apiSearchInspectorItem);
                    $insuranceApplications = $this->insuranceService->createInsuranceApplications($insurancePlan, $apiSearchInspectorItem);

                    if (! $this->addInsuranceApplication->insert($insuranceApplications)) {
                        throw new Exception('Failed to create insurance applications. Please try again later.');
                    }

                    $responseData = $this->insurancePlanDTO->setData($insurancePlan);
                    $this->insuranceService->dispatchSaveBookingInspector($request, $bookingInspector, $responseData);

                    unset($responseData['request']);
                    $responseAll[] = array_merge($responseData, ['message' => 'Insurance plan and related applications successfully created']);
                } catch (Throwable $e) {
                    $this->insuranceService->handleException($e, $bookingInspector, $originalRQ, $responseAll, $bookingItem);
                    $errCount++;
                }
            });
        }

        return $this->finalizeResponse($errCount, $responseAll, $request);
    }

    public function delete(InsuranceAddRequest $request): JsonResponse
    {
        $bookingId = $request->input('booking_id');
        $bookingItem = $request->input('booking_item');

        $bookingItems = $this->insuranceService->getBookingItems($bookingId, $bookingItem);
        if (empty($bookingItems)) {
            return $this->sendError('Either booking_id or booking_item must be provided', 400);
        }

        foreach ($bookingItems as $bookingItem) {
            $insurancePlan = InsurancePlan::where('booking_item', $bookingItem)->first();

            if (! $insurancePlan) {
                return $this->sendError('Insurance plan not found for booking item: '.$bookingItem, 404);
            }

            $this->deleteInsurance->handle($insurancePlan);

            [$bookingId, $filters, $supplierId, $apiBookingInspectorItem] = BookingRepository::getParams($request, $bookingItem);

            if (! $apiBookingInspectorItem) {
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

            $message = 'Insurance plan successfully deleted for booking item: '.$bookingItem;
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
            $bookingItems = $this->insuranceService->getBookingItems($bookingId, null);
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
            return $this->insurancePlanDTO->setData($insurancePlan);
        });

        return $this->sendResponse($insurancePlansDTO->toArray(), 'Insurance plans retrieved successfully', 200);
    }

    public function finalizeResponse(int $errCount, array $responseAll, $request): JsonResponse
    {
        if ($errCount > 0) {
            return $this->sendError('An error occurred while processing the insurance plan.', '', 500, $responseAll);
        } else {
            $responseAll = array_merge(['request' => $request->all()], ['insurances' => $responseAll]);

            return $this->sendResponse($responseAll, 'successfull', 201);
        }
    }
}
