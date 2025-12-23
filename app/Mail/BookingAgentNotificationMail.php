<?php

namespace App\Mail;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use App\Services\AdvisorCommissionService;
use App\Services\PdfGeneratorService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\Services\HotelBookingCheckQuoteService;
use Modules\HotelContentRepository\Models\Hotel;

class BookingAgentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** @var string */
    public string $bookingItem;

    public function __construct(string $bookingItem)
    {
        $this->bookingItem = $bookingItem;
    }

    public function build()
    {
        $bookingItem = ApiBookingItem::where('booking_item', $this->bookingItem)->firstOrFail();

        // Quote / confirmation number based on booking item
        $quoteNumber = ApiBookingInspectorRepository::getBookIdByBookingItem($bookingItem->booking_item);

        /** @var HotelBookingCheckQuoteService $service */
        $service         = app(HotelBookingCheckQuoteService::class);
        $dataReservation = $service->getDataFirstSearch($bookingItem);

        $searchRequest = $bookingItem->search->request ?? '{}';
        $searchArray   = json_decode($searchRequest, true) ?? [];

        $giataCode = $dataReservation[0]['giata_code'] ?? null;

        // Load hotel with product + descriptive sections (for perks)
        $hotel = Hotel::query()
            ->with(['product.descriptiveContentsSection.descriptiveType'])
            ->where('giata_code', $giataCode)
            ->first();

        $hotelName = $hotel?->product?->name ?? '[Hotel Name]';

        // ---- Totals across all rooms ----
        $totalNet   = 0.0;
        $totalTax   = 0.0;
        $totalFees  = 0.0;
        $totalPrice = 0.0;

        foreach ($dataReservation as $item) {
            $totalNet   += $item['total_net']   ?? 0;
            $totalTax   += $item['total_tax']   ?? 0;
            $totalFees  += $item['total_fees']  ?? 0;
            $totalPrice += $item['total_price'] ?? 0;
        }

        $currency     = Arr::get($dataReservation, '0.currency', 'USD');
        $taxesAndFees = $totalTax + $totalFees;

        // ---- Advisor commission (using helper/service) ----
        $subtotal = $totalPrice - $taxesAndFees;
        /** @var AdvisorCommissionService $advisorCommissionService */
        $advisorCommissionService = app(AdvisorCommissionService::class);
        $advisorCommission        = $advisorCommissionService->calculate($bookingItem, $subtotal);

        // ---- Dates / guests ----
        $checkinRaw  = Arr::get($searchArray, 'checkin');
        $checkoutRaw = Arr::get($searchArray, 'checkout');

        $checkinFormatted  = $checkinRaw ? Carbon::parse($checkinRaw)->format('m/d/Y') : null;
        $checkoutFormatted = $checkoutRaw ? Carbon::parse($checkoutRaw)->format('m/d/Y') : null;

        $roomsCount    = count($dataReservation);
        $adultsCount   = collect(Arr::get($searchArray, 'occupancy', []))->sum('adults');
        $childrenCount = collect(Arr::get($searchArray, 'occupancy', []))
            ->sum(fn ($o) => count(Arr::get($o, 'children_ages', [])));

        $guestInfo = sprintf('%d Room(s), %d Adults, %d Children', $roomsCount, $adultsCount, $childrenCount);

        // ---- Room / rate info (based on the first room) ----
        $mainRoomName   = Arr::get($dataReservation, '0.room_name');
        $rateRefundable = Arr::get($dataReservation, '0.cancellation_policies.0.penalty_start_date');
        $rateRefundable = $rateRefundable
            ? 'Refundable until ' . Carbon::parse($rateRefundable)->format('m/d/Y')
            : 'Non-Refundable';
        $rateMealPlan   = Arr::get($dataReservation, '0.meal_plan_name')
            ?? Arr::get($dataReservation, '0.meal_plan');

        // ---- Hero image with fallback ----
        $defaultHeroPath = Storage::url('hotel.webp');

        if ($hotel?->product?->hero_image) {
            $hotelPhotoPath = Storage::url($hotel->product->hero_image);
        } else {
            $hotelPhotoPath = $defaultHeroPath;
        }

        // ---- Perks ('  . env('APP_NAME') .  ' amenities) ----
        $perks = collect($hotel?->product?->descriptiveContentsSection ?? [])
            ->filter(function ($sec) {
                $name = trim($sec->descriptiveType->name ?? '');

                return $name === ''  . env('APP_NAME') .  ' Amenities';
            })
            ->filter(function ($sec) {
                $now    = now();
                $starts = $sec->start_date ? $sec->start_date->startOfDay() : null;
                $ends   = $sec->end_date ? $sec->end_date->endOfDay() : null;

                // Only keep perks that are currently active
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

        // ---- Logging (lightweight) ----
        logger('BookingAgentNotificationMail', [
            'quoteNumber'   => $quoteNumber,
            'hotelGiata'    => $hotel?->giata_code,
            'rooms_count'   => count($dataReservation),
            'advisor_commission' => $advisorCommission,
        ]);

        // ---- PDF payload ----
        $addr = $hotel?->address ?? [];

        $hotelAddress = implode(', ', array_filter([
            $addr['line_1']              ?? null,
            $addr['city']                ?? null,
            $addr['state_province_name'] ?? null,
            $addr['country_code']        ?? null,
        ]));

        $pdfData = [
            'hotel'    => $hotel,
            'hotelData' => [
                'name'    => $hotelName,
                'address' => $hotelAddress,
            ],

            // Totals
            'total_net'          => $totalNet,
            'total_tax'          => $totalTax,
            'total_fees'         => $totalFees,
            'total_price'        => $totalPrice,
            'currency'           => $currency,
            'taxes_and_fees'     => $taxesAndFees,
            'advisor_commission' => $advisorCommission,

            // Agency info (can be expanded later if needed)
            'agency' => [
                'booking_agent'       => ''  . env('APP_NAME') .  ' Tours',
                'booking_agent_email' => 'support@terramaretours.com',
            ],

            // Images
            'hotelPhotoPath' => $hotelPhotoPath,

            // Pills / rate info
            'checkin'         => $checkinFormatted,
            'checkout'        => $checkoutFormatted,
            'guest_info'      => $guestInfo,
            'main_room_name'  => $mainRoomName,
            'rate_refundable' => $rateRefundable,
            'rate_meal_plan'  => $rateMealPlan,

            // Perks
            'perks' => $perks,

            // Misc
            // For now we use quoteNumber as confirmation number; replace if real confirmation id appears.
            'confirmation_number' => $quoteNumber,
        ];

        /** @var PdfGeneratorService $pdfService */
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.advisor-confirmation', $pdfData);

        // ---- Build mail ----
        return $this->subject('Congratulations! Your '  . env('APP_NAME') .  ' Booking is Confirmed!')
            ->view('emails.booking.advisor-confirmation')
            ->with([
                // Basic hotel info for the HTML email
                'hotelName'   => $hotelName,
            ])
            ->attachData($pdfContent, 'AdvisorConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
