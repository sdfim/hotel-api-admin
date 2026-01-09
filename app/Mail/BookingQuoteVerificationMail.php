<?php

namespace App\Mail;

use App\Services\BookingEmailDataService;
use App\Services\PdfGeneratorService;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
        $this->denyUrl = $denyUrl;
        $this->bookingItem = $bookingItem;
        $this->agentData = $agentData;
    }

    public function build()
    {
        /** @var BookingEmailDataService $dataService */
        $dataService = app(BookingEmailDataService::class);
        $data = $dataService->getBookingData($this->bookingItem);

        // 3) Agent name fallback (kept custom logic for agent name if needed)
        $this->agentData['name'] = $data['userAgent'] ? $data['userAgent']->name : ($this->agentData['name'] ?? 'N/A');

        // 7) Cache confirmation date for 7 days
        $cacheKey = 'booking_confirmation_date_'.$this->bookingItem;
        $confirmationDate = Cache::get($cacheKey);

        if (! $confirmationDate) {
            $confirmationDate = now()->toDateString();
            Cache::put($cacheKey, $confirmationDate, now()->addDays(7));
        }

        // 8) Build PDF payload
        $pdfData = [
            'hotel' => $data['hotel'],
            'hotelData' => [
                'name' => $data['hotelName'],
                'address' => $data['hotelAddress'],
            ],

            // Totals
            'total_net' => $data['total_net'],
            'total_tax' => $data['total_tax'],
            'total_fees' => $data['total_fees'],
            'total_price' => $data['total_price'],
            'currency' => $data['currency'],
            'taxes_and_fees' => $data['taxesAndFees'],
            'advisor_commission' => $data['advisor_commission'],

            // Agency info
            'agency' => [
                'booking_agent' => $this->agentData['name'],
                'booking_agent_email' => $this->agentData['email'] ?? 'kshacknow@vidanta.com',
            ],

            // Images
            'hotelPhotoPath' => $data['hotelPhotoPath'],

            // Pills / rate info
            'checkin' => $data['checkinDate'],
            'checkout' => $data['checkoutDate'],
            'guest_info' => $data['guestInfo'],
            'main_room_name' => $data['mainRoomName'],
            'rate_refundable' => $data['rateRefundable'],
            'rate_meal_plan' => $data['rateMealPlan'],

            // Perks
            'perks' => $data['perks'],

            // Misc
            'confirmation_date' => $confirmationDate,
        ];

        // 9) Generate PDF
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.quote-confirmation', $pdfData);

        // 10) Cache PDF data for 7 days
        Cache::put('booking_pdf_data_'.$this->bookingItem, $pdfData, now()->addDays(7));

        // 11) Build mail with attached PDF
        return $this->subject('Your Quote from '.env('APP_NAME').': Please Confirm')
            ->view('emails.booking.email_verification')
            ->with([
                'verificationUrl' => $this->verificationUrl,
                'denyUrl' => $this->denyUrl,
                'agentData' => $this->agentData,
                'quoteNumber' => $data['quoteNumber'],
                'hotel' => $data['hotel'],
                'rooms' => $data['dataReservation'],
                'searchRequest' => $data['searchArray'],
                'perks' => $data['perks'],

                // Pre-calculated values for Blade
                'checkinDate' => $data['checkinDate'],
                'checkoutDate' => $data['checkoutDate'],
                'guestInfo' => $data['guestInfo'],
                'currency' => $data['currency'],
                'subtotal' => $data['subtotal'],
                'taxes' => $data['total_tax'],
                'fees' => $data['total_fees'],
                'totalPrice' => $data['total_price'],
                'advisorCommission' => $data['advisor_commission'],
            ])
            ->attachData($pdfContent, 'QuoteDetails.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
