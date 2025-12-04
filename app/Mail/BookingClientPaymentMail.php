<?php

namespace App\Mail;

use App\Models\ApiBookingsMetadata;
use App\Services\PdfGeneratorService;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class BookingClientPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public ?string $payment_url;

    public ?string $booking_id;

    public function __construct(string $payment_url, string $booking_id)
    {
        $this->payment_url = $payment_url;
        $this->booking_id = $booking_id;
    }

    public function build()
    {
        $hotelName = 'your hotel';
        $bookimgMetadata = ApiBookingsMetadata::where('booking_id', $this->booking_id)->first();
        $bookimgItemData = $bookimgMetadata?->booking_item_data;

        logger()->info('BookApiHandler | book | bookimg_item_data', ['data' => $bookimgItemData, 'booking_id' => $this->booking_id]);

        if ($bookimgItemData) {
            $hotelName = Arr::get($bookimgItemData, 'hotel_name');
        }

        $mail = $this->subject("Your Fora Advisor has booked you at $hotelName and your booking is ready for payment")
            ->view('emails.booking.client_payment')
            ->with([
                'payment_url' => $this->payment_url,
                'hotelName' => $hotelName,
            ]);

        // Cache the entire PDF data array for 7 days
        $pdfCacheKey = 'booking_pdf_data_'.$bookimgMetadata?->booking_item;
        $cachedPdfData = Cache::get($pdfCacheKey);

        $pdfContent = null;
        if ($cachedPdfData) {
            // Use cached PDF data for PDF generation
            $pdfService = app(PdfGeneratorService::class);
            $pdfContent = $pdfService->generateRaw('pdf.booking_ready', $cachedPdfData);
        }

        if ($pdfContent) {
            $mail->attachData($pdfContent, 'BookingConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
