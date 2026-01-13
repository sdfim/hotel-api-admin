<?php

namespace App\Mail;

use App\Repositories\ApiBookingInspectorRepository;
use App\Services\BookingEmailDataService;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingClientConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public string $bookingItem;

    public function __construct(string $bookingItem)
    {
        $this->bookingItem = $bookingItem;
    }

    public function build()
    {
        /** @var BookingEmailDataService $dataService */
        $dataService = app(BookingEmailDataService::class);
        $data = $dataService->getBookingData($this->bookingItem);

        $data['confirmationNumber'] = $this->bookingItem;
        $bookedId = ApiBookingInspectorRepository::getBookedId($this->bookingItem);
        if ($bookedId) {
            $data['confirmationNumber'] = $bookedId->metadata->supplier_booking_item_id;
        }

        /** @var PdfGeneratorService $pdfService */
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.client-confirmation', $data);

        return $this->subject('Your Booking Confirmation')
            ->view('emails.booking.client-confirmation')
            ->with($data)
            ->attachData($pdfContent, 'ClientConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
