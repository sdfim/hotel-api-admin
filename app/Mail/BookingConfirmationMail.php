<?php

namespace App\Mail;

use App\Services\BookingEmailDataService;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $bookingItem;

    public function __construct($bookingItem)
    {
        $this->bookingItem = $bookingItem;
    }

    public function build()
    {
        /** @var BookingEmailDataService $dataService */
        $dataService = app(BookingEmailDataService::class);
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

        // 🔹 генерируем PDF прямо здесь
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.booking-confirmation', $pdfData);

        // Save cache for 7 days using bookingItem as part of the key
        $cacheKey = 'booking_confirmation_pdf_data_'.$this->bookingItem;
        \Cache::put($cacheKey, $pdfData, now()->addDays(7));

        $emailData = [
            'hotel' => $data['hotel'],
            'bookingMeta' => $data['bookingMeta'],
            'rooms' => $data['dataReservation'],
            'searchRequest' => $data['searchArray'],
        ];

        // Save cache for 7 days using bookingItem as part of the key
        $cacheKey = 'booking_confirmation_email_date_'.$this->bookingItem;
        \Cache::put($cacheKey, $emailData, now()->addDays(7));

        return $this->subject('Your booking is confirmed')
            ->view('emails.booking.confirmation')
            ->with($emailData)
            ->attachData($pdfContent, 'BookingConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
