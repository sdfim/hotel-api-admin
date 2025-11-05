<?php

namespace App\Mail;

use App\Models\User;
use App\Repositories\ApiBookingInspectorRepository;
use App\Services\PdfGeneratorService;
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
    use Queueable, SerializesModels;

    public $verificationUrl;

    public $denyUrl;

    public $bookingItem;

    public $agentData;

    public function __construct($verificationUrl, $denyUrl, $bookingItem, $agentData)
    {
        $this->verificationUrl = $verificationUrl;
        $this->denyUrl = $denyUrl;
        $this->bookingItem = $bookingItem;
        $this->agentData = $agentData;
    }

    public function build()
    {
        // 1) Base data
        $bookingItem = \App\Models\ApiBookingItem::where('booking_item', $this->bookingItem)->firstOrFail();
        $quoteNumber = ApiBookingInspectorRepository::getBookIdByBookingItem($bookingItem->booking_item);

        $service = app(HotelBookingCheckQuoteService::class);
        $dataReservation = $service->getDataFirstSearch($bookingItem);
        $searchRequest = $bookingItem->search->request;
        $giata_code = $dataReservation[0]['giata_code'] ?? null;

        // 2) Eager-load hotel with product -> descriptive sections -> type
        $hotelData = Hotel::query()
            ->with(['product.descriptiveContentsSection.descriptiveType'])
            ->where('giata_code', $giata_code)
            ->first();

        // 3) Agent name fallback
        $user = User::where('email', $this->agentData['email'] ?? null)->first();
        $this->agentData['name'] = $user ? $user->name : 'N/A';

        $hotel = $hotelData;

        // 4) Totals across rooms
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

        // 5) Build perks (TerraMare Amenities) as clean list of strings
        //    - respects optional active window (start_date / end_date)
        //    - splits by newlines, bullets, semicolons, ". "
        $perks = collect($hotelData?->product?->descriptiveContentsSection ?? [])
            ->filter(function ($sec) {
                $name = trim($sec->descriptiveType->name ?? '');

                return $name === 'TerraMare Amenities';
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

        // 6) Logging (no heavy data)
        logger('BookingQuoteVerificationMail', [
            'verificationUrl' => $this->verificationUrl,
            'denyUrl' => $this->denyUrl,
            'agentEmail' => $this->agentData['email'] ?? null,
            'hotelGiata' => $hotelData?->giata_code,
            'rooms_count' => count($dataReservation),
        ]);

        // 7) Cache confirmation date for 7 days using bookingItem as part of the key
        $cacheKey = 'booking_confirmation_date_'.$this->bookingItem;
        $confirmationDate = \Cache::get($cacheKey);
        if (! $confirmationDate) {
            $confirmationDate = now()->toDateString();
            \Cache::put($cacheKey, $confirmationDate, now()->addDays(7));
        }

        // 8) Build PDF payload (address composed safely)
        $addr = $hotel?->address ?? [];
        $hotelAddress = implode(', ', array_filter([
            $addr['line_1'] ?? null,
            $addr['city'] ?? null,
            $addr['state_province_name'] ?? null,
            $addr['country_code'] ?? null,
        ]));

        $pdfData = [
            'hotel' => $hotel,
            'hotelData' => [
                'name' => $hotel?->product?->name ?? 'Unknown Hotel',
                'address' => $hotelAddress,
            ],
            'total_net' => $total_net,
            'total_tax' => $total_tax,
            'total_fees' => $total_fees,
            'total_price' => $total_price,
            'agency' => [
                'booking_agent' => Arr::get($this->agentData, 'name') ?? 'OLIVER SHACKNOW',
                'booking_agent_email' => Arr::get($this->agentData, 'email') ?? 'kshacknow@terramare.com',
            ],
            'hotelPhotoPath' => $hotel?->product?->hero_image ? Storage::url($hotel->product->hero_image) : '',
            'confirmation_date' => $confirmationDate,
        ];

        // 9) Generate PDF
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.quote-confirmation', $pdfData);

        // 10) Cache PDF data for 7 days
        \Cache::put('booking_pdf_data_'.$this->bookingItem, $pdfData, now()->addDays(7));

        // 11) Build mail
        return $this->subject('Confirm the Quote')
            ->view('emails.booking.email_verification')
            ->with([
                'verificationUrl' => $this->verificationUrl,
                'denyUrl' => $this->denyUrl,
                'agentData' => $this->agentData,
                'quoteNumber' => $quoteNumber,
                'hotel' => $hotelData,
                'rooms' => $dataReservation,
                'searchRequest' => json_decode($searchRequest, true),
                'perks' => $perks, // << pass prepared perks to Blade
            ])
            ->attachData($pdfContent, 'QuoteDetails.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
