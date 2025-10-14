<?php

namespace App\Mail;

use App\Models\ApiBookingsMetadata;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class BookingClientPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $payment_url;

    public $booking_id;

    public function __construct($payment_url, $booking_id)
    {
        $this->payment_url = $payment_url;
        $this->booking_id = $booking_id;
    }

    public function build()
    {
        $hotelName = null;
        $bookimgMetadata = ApiBookingsMetadata::where('booking_id', $this->booking_id)->first();
        $bookimgItemData = $bookimgMetadata?->booking_item_data;

        logger()->info('BookApiHandler | book | bookimg_item_data', ['data' => $bookimgItemData, 'booking_id' => $this->booking_id]);

        if ($bookimgItemData) {
            $hotelName = Arr::get($bookimgItemData, 'hotel_name');
        }

        $mail = $this->subject('Your booking is ready for payment')
            ->view('emails.booking.client_payment')
            ->with([
                'payment_url' => $this->payment_url,
                'hotelName' => $hotelName,
            ]);

        // Cache the entire PDF data array for 7 days
        $pdfCacheKey = 'booking_pdf_data_'.$bookimgMetadata?->booking_item;
        $cachedPdfData = \Cache::get($pdfCacheKey);

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
