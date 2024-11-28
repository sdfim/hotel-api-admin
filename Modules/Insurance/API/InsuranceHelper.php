<?php

namespace Modules\Insurance\API;

use App\Jobs\SaveBookingInspector;
use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository as BookingRepository;
use App\Repositories\ApiBookingItemRepository;
use App\Repositories\ApiSearchInspectorRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\HotelContentRepository\Models\Vendor;
use Modules\Insurance\Models\InsurancePlan;
use Modules\Insurance\Models\InsuranceProvider;
use Modules\Insurance\Models\InsuranceRateTier;
use Modules\Insurance\Models\InsuranceRestriction;

trait InsuranceHelper
{
    private function dispatchSaveBookingInspector($request, $bookingInspector, $responseData): void
    {
        $originalRQ = [
            'path' => $request->path(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ];
        $content['original']['request'] = $originalRQ;

        SaveBookingInspector::dispatch($bookingInspector, $content, $responseData);
    }

    private function getInsuranceProvider(string $providerName): ?Vendor
    {
        return Vendor::where('name', $providerName)->first();
    }

    private function getBookingItems(?string $bookingId, ?string $bookingItem): array
    {
        if ($bookingId) {
            $bookingItemsCollection = BookingRepository::notBookedItems($bookingId);
            return $bookingItemsCollection->pluck('booking_item')->toArray();
        } elseif ($bookingItem) {
            return [$bookingItem];
        }
        return [];
    }

    private function createInsurancePlan($request, $bookingItem, $insuranceProvider, $itemPricing, $apiSearchInspectorItem): InsurancePlan
    {
        $insurancePlan = new InsurancePlan();
        $insurancePlan->booking_item = $bookingItem;

        $totalPassengersNumber = ApiSearchInspectorRepository::getTotalOccupancy($apiSearchInspectorItem['occupancy']);
        $bookingItemTotalPrice = (float)Arr::get($itemPricing, 'total_price', 0);
        $costPerPassenger = $totalPassengersNumber > 0 ? $bookingItemTotalPrice / $totalPassengersNumber : 0;

        $insuranceRateTier = InsuranceRateTier::where('vendor_id', $insuranceProvider->id)
            ->where('min_trip_cost', '<=', $costPerPassenger)
            ->where('max_trip_cost', '>=', $costPerPassenger)
            ->first();

        if (!$insuranceRateTier) {
            throw new Exception('No applicable insurance rate tier found.');
        }

        $totalPlanCost = $insuranceRateTier->net_to_trip_mate * $totalPassengersNumber;
        $commissionUjv = $insuranceRateTier->ujv_retention * $totalPassengersNumber;
        $insuranceVendorFee = $insuranceRateTier->consumer_plan_cost * $totalPassengersNumber;

        $insurancePlan->total_insurance_cost = $totalPlanCost;
        $insurancePlan->insurance_vendor_fee = $insuranceVendorFee;
        $insurancePlan->commission_ujv = $commissionUjv;
        $insurancePlan->vendor_id = $insuranceProvider->id;
        $insurancePlan->request = $request->all();

        if (!$insurancePlan->save()) {
            throw new Exception('Failed to create the insurance plan. Please try again later.');
        }

        return $insurancePlan;
    }

    private function createInsuranceApplications($insurancePlan, $apiSearchInspectorItem): array
    {
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

        return $insuranceApplications;
    }

    private function handleException(Exception $e, $bookingInspector, $originalRQ, &$responceAll, $bookingItem): void
    {
        $message = "An error occurred while creating the insurance plan: {$e->getMessage()}";
        Log::error($message);
        Log::error($e->getTraceAsString());

        $content = [];
        if ($bookingInspector !== null) {
            $content['original']['response'] = $bookingInspector;
        }
        if ($originalRQ !== null) {
            $content['original']['request'] = $originalRQ;
        }

        SaveBookingInspector::dispatch($bookingInspector, $content, [], 'error', ['side' => 'supplier', 'message' => $message]);

        $responceAll[] = [
            'booking_item' => $bookingItem,
            'message' => $message
        ];
    }

    private function finalizeResponse(int $errCount, array $responceAll, $request): JsonResponse
    {
        if ($errCount > 0) {
            return $this->sendError('An error occurred while processing the insurance plan.', '', 500, $responceAll);
        } else {
            $responceAll = array_merge(['request' => $request->all()], ['insurances' => $responceAll]);
            return $this->sendResponse($responceAll, 'successfull', 201);
        }
    }

    private function validateBookingItem($bookingItem, $vendorId): array
    {
        $validationRules = InsuranceRestriction::with('restrictionType')
            ->where('vendor_id', $vendorId)
            ->get()
            ->map(function ($restriction) {
                return [
                    'vendor' => $restriction->vendor->name,
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
