<?php

namespace App\Mail;

use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationForAgentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $bookingItem;

    public function __construct($bookingItem)
    {
        $this->bookingItem = $bookingItem;
    }

    public function build()
    {
        $cacheKey = 'booking_confirmation_pdf_data_'.$this->bookingItem;
        $pdfData = \Cache::get($cacheKey);

        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.booking-confirmation', $pdfData);

        $cacheKey = 'booking_confirmation_email_date_'.$this->bookingItem;
        $emailData = \Cache::get($cacheKey);

        return $this->subject('Booking is Confirmed')
            ->view('emails.booking.confirmation-for-agent')
            ->with($emailData)
            ->attachData($pdfContent, 'BookingConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
