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

        $cacheKey = 'booking_confirmation_date_' . $this->bookingItem;
        $confirmationDate = Cache::get($cacheKey);

        if (!$confirmationDate) {
            $confirmationDate = now()->toDateString();
            Cache::put($cacheKey, $confirmationDate, now()->addDays(7));
        }

        $data['confirmation_date'] = $confirmationDate;

        // Sync agent name if found via userAgent in service
        if (!empty($data['userAgent'])) {
            $this->agentData['name'] = $data['userAgent']->name;
            $data['agency']['booking_agent'] = $data['userAgent']->name;
            $data['agency']['booking_agent_email'] = $data['userAgent']->email;
        }

        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.quote-confirmation', $data);

        Cache::put('booking_pdf_data_' . $this->bookingItem, $data, now()->addDays(7));

        return $this->subject('Your Quote from ' . env('APP_NAME') . ': Please Confirm')
            ->view('emails.booking.email_verification')
            ->with(array_merge($data, [
                'verificationUrl' => $this->verificationUrl,
                'denyUrl' => $this->denyUrl,
                'agentData' => $this->agentData,
            ]))
            ->attachData($pdfContent, 'QuoteDetails.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
