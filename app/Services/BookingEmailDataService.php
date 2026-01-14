<?php

namespace App\Services;

use App\Models\ApiBookingItem;
use App\Models\User;
use App\Repositories\ApiBookingInspectorRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\API\Services\HotelBookingCheckQuoteService;
use Modules\HotelContentRepository\Models\Hotel;

class BookingEmailDataService
{
    /**
     * Get all necessary data for booking emails, cached for 10 minutes.
     */
    public function getBookingData(string $bookingItemHandle): array
    {
        if (Cache::has('booking_email_common_data_'.$bookingItemHandle)) {
            $cachedData = Cache::get('booking_email_common_data_'.$bookingItemHandle);
            \Log::info('BookingEmailDataServiceData _ found in cache', ['data' => $cachedData]);

            return $cachedData;
        } else {
            \Log::info('BookingEmailDataService _ Data not found in cache, fetching new data', ['bookingItemHandle' => $bookingItemHandle]);
        }

        return Cache::remember('booking_email_common_data_'.$bookingItemHandle, 600, function () use ($bookingItemHandle) {
            /** @var ApiBookingItem|null $bookingItem */
            $bookingItem = ApiBookingItem::where('booking_item', $bookingItemHandle)->first();

            $quoteNumber = ApiBookingInspectorRepository::getBookIdByBookingItem($bookingItemHandle);

            /** @var HotelBookingCheckQuoteService $service */
            $service = app(HotelBookingCheckQuoteService::class);
            $dataReservation = $service->getDataFirstSearch($bookingItem);

            $searchRequest = $bookingItem->search->request ?? '{}';
            $searchArray = json_decode($searchRequest, true) ?? [];

            $giataCode = $dataReservation[0]['giata_code'] ?? null;

            $hotel = Hotel::query()
                ->with(['product.descriptiveContentsSection.descriptiveType'])
                ->where('giata_code', $giataCode)
                ->first();

            $total_net = 0;
            $total_tax = 0;
            $total_fees = 0;
            $total_price = 0;

            foreach ($dataReservation as $item) {
                $total_net += $item['total_net'] ?? 0;
                $total_tax += $item['total_tax'] ?? 0;
                $total_fees += $item['total_fees'] ?? 0;
                $total_price += $item['total_price'] ?? 0;
            }

            $currency = Arr::get($dataReservation, '0.currency', 'USD');
            $taxesAndFees = $total_tax + $total_fees;
            $subtotal = $total_price - $taxesAndFees;

            /** @var AdvisorCommissionService $advisorCommissionService */
            $advisorCommissionService = app(AdvisorCommissionService::class);
            $advisorCommission = $advisorCommissionService->calculate($bookingItem, $subtotal);

            $checkinFormatted = optional(Carbon::parse(Arr::get($searchArray, 'checkin')))->format('m/d/Y');
            $checkoutFormatted = optional(Carbon::parse(Arr::get($searchArray, 'checkout')))->format('m/d/Y');

            $roomsCount = count($dataReservation);
            $adultsCount = collect(Arr::get($searchArray, 'occupancy', []))->sum('adults');
            $childrenCount = collect(Arr::get($searchArray, 'occupancy', []))
                ->sum(fn ($o) => count(Arr::get($o, 'children_ages', [])));

            $guestInfo = sprintf('%d Room(s), %d Adults, %d Children', $roomsCount, $adultsCount, $childrenCount);

            // Room / rate info
            $firstRoom = $dataReservation[0] ?? [];
            $refundableUntil = '';
            $cancellationPolicies = Arr::get($firstRoom, 'cancellation_policies', []);
            foreach ($cancellationPolicies as $policy) {
                if (Arr::get($policy, 'description') === 'General Cancellation Policy' || Arr::get($policy, 'penalty_start_date')) {
                    $penaltyStartDate = Arr::get($policy, 'penalty_start_date');
                    if ($penaltyStartDate) {
                        $refundableUntil = 'Refundable until '.Carbon::parse($penaltyStartDate)->format('m/d/Y');
                        break;
                    }
                }
            }
            if (empty($refundableUntil)) {
                $refundableUntil = 'Refundable';
            }

            $mealPlanSummary = Arr::get($firstRoom, 'meal_plan_name') ?? Arr::get($firstRoom, 'meal_plan');
            if (empty($mealPlanSummary)) {
                if (! empty($hotel->hotel_board_basis)) {
                    $mealPlanSummary = is_array($hotel->hotel_board_basis)
                        ? implode(', ', $hotel->hotel_board_basis)
                        : $hotel->hotel_board_basis;
                } else {
                    $mealPlanSummary = 'All-Inclusive Meal Plan';
                }
            }

            $hotelPhotoPath = ($hotel?->product?->hero_image) ? Storage::url($hotel->product->hero_image) : null;

            $room = $hotel?->rooms->where('name', Arr::get($firstRoom, 'room_name'))->first();
            $roomImage = $room ? $room->galleries->flatMap(fn ($g) => $g->images)->first()?->image_url : null;
            $roomPhotoPath = $roomImage ? Storage::url($roomImage) : $hotelPhotoPath;

            // Perks
            $perks = collect($hotel?->product?->descriptiveContentsSection ?? [])
                ->filter(function ($sec) {
                    $name = trim($sec->descriptiveType->name ?? '');

                    return $name === ''.env('APP_NAME').' Amenities';
                })
                ->filter(function ($sec) {
                    $now = now();
                    $starts = $sec->start_date ? $sec->start_date->startOfDay() : null;
                    $ends = $sec->end_date ? $sec->end_date->endOfDay() : null;

                    return (! $starts || $now->gte($starts)) && (! $ends || $now->lte($ends));
                })
                ->sortByDesc(fn ($sec) => optional($sec->start_date)->timestamp ?? 0)
                ->pluck('value')
                ->map(fn ($v) => strip_tags((string) $v))
                ->flatMap(fn ($text) => preg_split('/\r\n|\r|\n|•\s*|;\s*|\. +/u', $text) ?: [])
                ->map(fn ($s) => trim($s, " \t\n\r\0\x0B•;,."))
                ->filter()
                ->values()
                ->all();

            $addr = $hotel?->address ?? [];
            $hotelAddress = implode(', ', array_filter([
                $addr['line_1'] ?? null,
                $addr['city'] ?? null,
                $addr['state_province_name'] ?? null,
                $addr['country_code'] ?? null,
            ]));

            // Advisor info for confirmed bookings
            $booking = ApiBookingInspectorRepository::getBookItemsByBookingItem($bookingItemHandle);
            $bookingMeta = $booking?->metadata ?? [];
            $requestBooking = json_decode($booking?->request ?? '', true) ?? [];
            $agentId = Arr::get($requestBooking, 'api_client.id');
            $agentEmail = Arr::get($requestBooking, 'api_client.email');
            $userAgent = User::where('id', $agentId)
                ->orWhere('email', $agentEmail)
                ->first();

            $clientLastName = Arr::get($requestBooking, 'booking_contact.last_name');
            $clientFirstName = Arr::get($requestBooking, 'booking_contact.first_name');

            // Agent Default Info
            $agentName = $userAgent->name ?? 'N/A';
            $agentEmail = $userAgent->email ?? 'support@terramaretours.com';

            return [
                'bookingItem' => $bookingItem,
                'quoteNumber' => $quoteNumber,
                'dataReservation' => $dataReservation,
                'rooms' => $dataReservation,
                'searchArray' => $searchArray,
                'searchRequest' => $searchArray,
                'hotel' => $hotel,
                'hotelName' => $hotel?->product?->name ?? 'Unknown Hotel',
                'hotelAddress' => $hotelAddress,
                'hotelData' => [
                    'name' => $hotel?->product?->name ?? 'Unknown Hotel',
                    'address' => $hotelAddress,
                ],
                'agency' => [
                    'booking_agent' => ''.env('APP_NAME').' Tours',
                    'booking_agent_email' => 'support@terramaretours.com',
                ],
                'heroImage' => $hotelPhotoPath, // matching email_verification.blade.php
                'secondaryImage' => $roomPhotoPath, // matching PDF
                'totalNet' => $total_net,
                'total_tax' => $total_tax,
                'totalTax' => $total_tax,
                'taxes' => $total_tax,
                'total_fees' => $total_fees,
                'totalFees' => $total_fees,
                'fees' => $total_fees,
                'total_price' => $total_price,
                'totalPrice' => $total_price,
                'currency' => $currency,
                'taxesAndFees' => $taxesAndFees,
                'subtotal' => $subtotal,
                'advisorCommission' => $advisorCommission,
                'checkinDate' => $checkinFormatted,
                'checkoutDate' => $checkoutFormatted,
                'checkin' => $checkinFormatted,
                'checkout' => $checkoutFormatted,
                'guestInfo' => $guestInfo,
                'mainRoomName' => Arr::get($firstRoom, 'room_name'),
                'rateRefundable' => $refundableUntil,
                'refundableUntil' => $refundableUntil,
                'rateMealPlan' => $mealPlanSummary,
                'mealPlanSummary' => $mealPlanSummary,
                'perks' => $perks,
                'userAgent' => $userAgent,
                'bookingMeta' => $bookingMeta,
                'clientFirstName' => $clientFirstName,
                'clientLastName' => $clientLastName,
                'guestName' => trim($clientFirstName.' '.$clientLastName),
                'agentName' => $agentName,
                'agentEmail' => $agentEmail,

                // Aliases for nested (legacy support)
                'agencyName' => ($userAgent ? $userAgent->name : env('APP_NAME').' Tours'),
                'agencyEmail' => ($userAgent ? $userAgent->email : 'support@vidanta.com'),
            ];
        });
    }
}
