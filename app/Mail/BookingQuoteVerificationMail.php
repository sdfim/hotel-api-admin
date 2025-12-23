<?php

namespace App\Mail;

use App\Models\ApiBookingItem;
use App\Models\User;
use App\Repositories\ApiBookingInspectorRepository;
use App\Services\AdvisorCommissionService;
use App\Services\PdfGeneratorService;
use Cache;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Modules\API\Services\HotelBookingCheckQuoteService;
use Modules\HotelContentRepository\Models\Hotel;

class BookingQuoteVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public ?string $verificationUrl;
    public ?string $denyUrl;
    public ?string $bookingItem;
    public ?array $agentData;

    public function __construct(string $verificationUrl, string $denyUrl, string $bookingItem, array $agentData)
    {
        $this->verificationUrl = $verificationUrl;
        $this->denyUrl         = $denyUrl;
        $this->bookingItem     = $bookingItem;
        $this->agentData       = $agentData;
    }

    public function build()
    {
        // 1) Base booking data
        $bookingItem = ApiBookingItem::where('booking_item', $this->bookingItem)->firstOrFail();
        $quoteNumber = ApiBookingInspectorRepository::getBookIdByBookingItem($bookingItem->booking_item);

        $service         = app(HotelBookingCheckQuoteService::class);
        $dataReservation = $service->getDataFirstSearch($bookingItem);
        $searchRequest   = $bookingItem->search->request;
        $searchArray     = json_decode($searchRequest, true) ?? [];

        $giataCode = $dataReservation[0]['giata_code'] ?? null;

        // 2) Load hotel with related product + descriptive sections
        $hotelData = Hotel::query()
            ->with(['product.descriptiveContentsSection.descriptiveType'])
            ->where('giata_code', $giataCode)
            ->first();

        $hotel = $hotelData;

        // 3) Agent name fallback
        $user = User::where('email', $this->agentData['email'] ?? null)->first();
        $this->agentData['name'] = $user ? $user->name : 'N/A';

        // 4) Totals across all rooms
        $total_net   = 0;
        $total_tax   = 0;
        $total_fees  = 0;
        $total_price = 0;

        foreach ($dataReservation as $item) {
            $total_net   += $item['total_net']   ?? 0;
            $total_tax   += $item['total_tax']   ?? 0;
            $total_fees  += $item['total_fees']  ?? 0;
            $total_price += $item['total_price'] ?? 0;
        }

        // 4.1) Extra totals and currency
        $currency     = Arr::get($dataReservation, '0.currency', 'USD');
        $taxesAndFees = $total_tax + $total_fees;

        // 4.2) Advisor commission
        $subtotal = $total_price - $taxesAndFees;
        /** @var AdvisorCommissionService $advisorCommissionService */
        $advisorCommissionService = app(AdvisorCommissionService::class);
        $advisorCommission        = $advisorCommissionService->calculate($bookingItem, $subtotal);

        // 4.3) Dates / guests for pills
        $checkinRaw  = Arr::get($searchArray, 'checkin');
        $checkoutRaw = Arr::get($searchArray, 'checkout');

        $checkinFormatted  = $checkinRaw ? Carbon::parse($checkinRaw)->format('m/d/Y') : null;
        $checkoutFormatted = $checkoutRaw ? Carbon::parse($checkoutRaw)->format('m/d/Y') : null;

        $roomsCount    = count($dataReservation);
        $adultsCount   = collect(Arr::get($searchArray, 'occupancy', []))->sum('adults');
        $childrenCount = collect(Arr::get($searchArray, 'occupancy', []))
            ->sum(fn ($o) => count(Arr::get($o, 'children_ages', [])));

        $guestInfo = sprintf('%d Room(s), %d Adults, %d Children', $roomsCount, $adultsCount, $childrenCount);

        // 4.4) Room / rate info (null-safe if fields are missing)
        $mainRoomName   = Arr::get($dataReservation, '0.room_name');
        $rateRefundable = Arr::get($dataReservation, '0.cancellation_policies.0.penalty_start_date');
        $rateRefundable = $rateRefundable ? 'Refundable until ' . $rateRefundable : 'Non-Refundable';
        $rateMealPlan   = Arr::get($dataReservation, '0.meal_plan_name')
            ?? Arr::get($dataReservation, '0.meal_plan');

        logger('Rate Info', [
            'mainRoomName'   => $mainRoomName,
            'rateRefundable' => $rateRefundable,
            'rateMealPlan'   => $rateMealPlan,
            'dataReservation' => $dataReservation,
        ]);

        // 4.5) Hero photo with proper fallback
        $defaultHeroPath = Storage::url('hotel.webp');

        if ($hotel?->product?->hero_image) {
            $hotelPhotoPath = Storage::url($hotel->product->hero_image);
        } else {
            $hotelPhotoPath = $defaultHeroPath;
        }

        // 5) Build perks (TerraMare Amenities) as a clean list of strings
        $perks = collect($hotelData?->product?->descriptiveContentsSection ?? [])
            ->filter(function ($sec) {
                $name = trim($sec->descriptiveType->name ?? '');

                return $name === 'TerraMare Amenities';
            })
            ->filter(function ($sec) {
                $now    = now();
                $starts = $sec->start_date ? $sec->start_date->startOfDay() : null;
                $ends   = $sec->end_date ? $sec->end_date->endOfDay() : null;

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

        // 6) Logging (no heavy data)
        logger('BookingQuoteVerificationMail', [
            'verificationUrl' => $this->verificationUrl,
            'denyUrl'         => $this->denyUrl,
            'agentEmail'      => $this->agentData['email'] ?? null,
            'hotelGiata'      => $hotelData?->giata_code,
            'rooms_count'     => count($dataReservation),
        ]);

        // 7) Cache confirmation date for 7 days using bookingItem as part of the key
        $cacheKey         = 'booking_confirmation_date_'.$this->bookingItem;
        $confirmationDate = Cache::get($cacheKey);

        if (! $confirmationDate) {
            $confirmationDate = now()->toDateString();
            Cache::put($cacheKey, $confirmationDate, now()->addDays(7));
        }

        // 8) Build PDF payload
        $addr = $hotel?->address ?? [];

        $hotelAddress = implode(', ', array_filter([
            $addr['line_1']              ?? null,
            $addr['city']                ?? null,
            $addr['state_province_name'] ?? null,
            $addr['country_code']        ?? null,
        ]));

        $pdfData = [
            'hotel' => $hotel,
            'hotelData' => [
                'name'    => $hotel?->product?->name ?? 'Unknown Hotel',
                'address' => $hotelAddress,
            ],

            // Totals
            'total_net'           => $total_net,
            'total_tax'           => $total_tax,
            'total_fees'          => $total_fees,
            'total_price'         => $total_price,
            'currency'            => $currency,
            'taxes_and_fees'      => $taxesAndFees,
            'advisor_commission'  => $advisorCommission,

            // Agency info
            'agency' => [
                'booking_agent'       => Arr::get($this->agentData, 'name')  ?? 'OLIVER SHACKNOW',
                'booking_agent_email' => Arr::get($this->agentData, 'email') ?? 'kshacknow@terramare.com',
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
            'confirmation_date' => $confirmationDate,
        ];

        // 9) Generate PDF
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.quote-confirmation', $pdfData);

        // 10) Cache PDF data for 7 days
        Cache::put('booking_pdf_data_'.$this->bookingItem, $pdfData, now()->addDays(7));

        // 11) Build mail with attached PDF
        return $this->subject('Your Quote from Terra Mare: Please Confirm')
            ->view('emails.booking.email_verification')
            ->with([
                'verificationUrl'    => $this->verificationUrl,
                'denyUrl'            => $this->denyUrl,
                'agentData'          => $this->agentData,
                'quoteNumber'        => $quoteNumber,
                'hotel'              => $hotelData,
                'rooms'              => $dataReservation,
                'searchRequest'      => $searchArray,
                'perks'              => $perks,

                // Pre-calculated values for Blade
                'checkinDate'        => $checkinFormatted,
                'checkoutDate'       => $checkoutFormatted,
                'guestInfo'          => $guestInfo,
                'currency'           => $currency,
                'subtotal'           => $subtotal,
                'taxes'              => $total_tax,
                'fees'               => $total_fees,
                'totalPrice'         => $total_price,
                'advisorCommission'  => $advisorCommission,
            ])
            ->attachData($pdfContent, 'QuoteDetails.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
