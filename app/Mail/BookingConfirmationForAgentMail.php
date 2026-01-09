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
        /** @var \App\Services\BookingEmailDataService $dataService */
        $dataService = app(\App\Services\BookingEmailDataService::class);
        $data = $dataService->getBookingData($this->bookingItem);

        $pdfData = [
            'customerName' => 'Mr '.$data['clientFirstName'].' '.$data['clientLastName'],
            'hotel' => $data['hotel'],
            'hotelData' => [
                'name' => $data['hotelName'],
                'address' => $data['hotelAddress'],
            ],
            'total_net' => $data['total_net'],
            'total_tax' => $data['total_tax'],
            'total_fees' => $data['total_fees'],
            'total_price' => $data['total_price'],
            'agency' => [
                'booking_agent' => $data['userAgent']->name ?? 'OLIVER SHACKNOW',
                'booking_agent_email' => $data['userAgent']->email ?? 'kshacknow@vidanta.com',
            ],
            'hotelPhotoPath' => $data['hotelPhotoPath'],
        ];

        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.booking-confirmation', $pdfData);

        $emailData = [
            'hotel' => $data['hotel'],
            'bookingMeta' => $data['bookingMeta'],
            'rooms' => $data['dataReservation'],
            'searchRequest' => $data['searchArray'],
        ];

        return $this->subject('Booking is Confirmed')
            ->view('emails.booking.confirmation-for-agent')
            ->with($emailData)
            ->attachData($pdfContent, 'BookingConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
