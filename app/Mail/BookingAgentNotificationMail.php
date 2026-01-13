<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Repositories\ApiBookingInspectorRepository;
use App\Services\BookingEmailDataService;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingAgentNotificationMail extends Mailable implements ShouldQueue
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

        $data['guestName'] = Reservation::where('booking_item', $this->bookingItem)
            ->value('passenger_surname') ?? '';

        /** @var PdfGeneratorService $pdfService */
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.advisor-confirmation', $data);

        return $this->subject('Congratulations! Your ' . env('APP_NAME') . ' Booking is Confirmed!')
            ->view('emails.booking.advisor-confirmation')
            ->with($data)
            ->attachData($pdfContent, 'AdvisorConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
