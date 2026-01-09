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
        return Cache::remember('booking_email_common_data_'.$bookingItemHandle, 600, function () use ($bookingItemHandle) {
            /** @var ApiBookingItem|null $bookingItem */
            $bookingItem = ApiBookingItem::where('booking_item', $bookingItemHandle)->first();
            if (! $bookingItem) {
                // Try to find in metadata if not in items (some items move to metadata after booking)
                $metadata = \App\Models\ApiBookingsMetadata::where('booking_item', $bookingItemHandle)->first();
                if (! $metadata) {
                    throw new \Exception('Booking item not found: '.$bookingItemHandle);
                }
                // We still need the original ApiBookingItem for search relation etc.
                // If it's not found, we might have a problem with some methods.
                // But usually it should be there.
            }

            $quoteNumber = ApiBookingInspectorRepository::getBookIdByBookingItem($bookingItemHandle);

            /** @var HotelBookingCheckQuoteService $service */
            $service = app(HotelBookingCheckQuoteService::class);
            $dataReservation = $service->getDataFirstSearch($bookingItem);

            $searchRequest = $bookingItem->search->request ?? '{}';
            $searchArray = json_decode($searchRequest, true) ?? [];

            $giataCode = $dataReservation[0]['giata_code'] ?? null;

            // Load hotel with related product + descriptive sections
            $hotel = Hotel::query()
                ->with(['product.descriptiveContentsSection.descriptiveType'])
                ->where('giata_code', $giataCode)
                ->first();

            // Totals
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

            // Dates / guests
            $checkinRaw = Arr::get($searchArray, 'checkin');
            $checkoutRaw = Arr::get($searchArray, 'checkout');

            $checkinFormatted = $checkinRaw ? Carbon::parse($checkinRaw)->format('m/d/Y') : null;
            $checkoutFormatted = $checkoutRaw ? Carbon::parse($checkoutRaw)->format('m/d/Y') : null;

            $roomsCount = count($dataReservation);
            $adultsCount = collect(Arr::get($searchArray, 'occupancy', []))->sum('adults');
            $childrenCount = collect(Arr::get($searchArray, 'occupancy', []))
                ->sum(fn ($o) => count(Arr::get($o, 'children_ages', [])));

            $guestInfo = sprintf('%d Room(s), %d Adults, %d Children', $roomsCount, $adultsCount, $childrenCount);

            // Room / rate info
            $mainRoomName = Arr::get($dataReservation, '0.room_name');
            $rateRefundable = Arr::get($dataReservation, '0.cancellation_policies.0.penalty_start_date');
            $rateRefundable = $rateRefundable
                ? 'Refundable until '.Carbon::parse($rateRefundable)->format('m/d/Y')
                : 'Refundable';
            $rateMealPlan = Arr::get($dataReservation, '0.meal_plan_name')
                ?? Arr::get($dataReservation, '0.meal_plan');

            // Hero photo
            $defaultHeroPath = Storage::url('hotel.webp');
            $hotelPhotoPath = ($hotel?->product?->hero_image) ? Storage::url($hotel->product->hero_image) : $defaultHeroPath;

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

            return [
                'bookingItem' => $bookingItem,
                'quoteNumber' => $quoteNumber,
                'dataReservation' => $dataReservation,
                'searchArray' => $searchArray,
                'hotel' => $hotel,
                'hotelName' => $hotel?->product?->name ?? 'Unknown Hotel',
                'hotelAddress' => $hotelAddress,
                'hotelPhotoPath' => $hotelPhotoPath,
                'total_net' => $total_net,
                'total_tax' => $total_tax,
                'total_fees' => $total_fees,
                'total_price' => $total_price,
                'currency' => $currency,
                'taxesAndFees' => $taxesAndFees,
                'subtotal' => $subtotal,
                'advisor_commission' => $advisorCommission,
                'checkinDate' => $checkinFormatted,
                'checkoutDate' => $checkoutFormatted,
                'guestInfo' => $guestInfo,
                'mainRoomName' => $mainRoomName,
                'rateRefundable' => $rateRefundable,
                'rateMealPlan' => $rateMealPlan,
                'perks' => $perks,
                'userAgent' => $userAgent,
                'bookingMeta' => $bookingMeta,
                'clientFirstName' => $clientFirstName,
                'clientLastName' => $clientLastName,
            ];
        });
    }
}
