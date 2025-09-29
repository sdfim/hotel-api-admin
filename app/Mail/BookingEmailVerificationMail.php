<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\API\Services\HotelBookingCheckQuoteService;
use Modules\HotelContentRepository\Models\Hotel;

class BookingEmailVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $verificationUrl;

    public $bookingItem;

    public function __construct($verificationUrl, $bookingItem)
    {
        $this->verificationUrl = $verificationUrl;
        $this->bookingItem = $bookingItem;
    }

    public function build()
    {
        $bookingItem = \App\Models\ApiBookingItem::where('booking_item', $this->bookingItem)->first();
        $service = app(HotelBookingCheckQuoteService::class);
        $dataReservation = $service->getDataFirstSearch($bookingItem);
        $searchRequest = $bookingItem->search->request;
        $giata_code = $dataReservation[0]['giata_code'] ?? null;
        $hotelData = Hotel::where('giata_code', $giata_code)->first();

        logger('BookingEmailVerificationMail', [
            'verificationUrl' => $this->verificationUrl,
            'hotel' => $hotelData,
            'rooms' => $dataReservation,
            'searchRequest' => json_decode($searchRequest, true),
        ]);

        return $this->subject('Confirm your booking')
            ->view('emails.booking.email_verification')
            ->with([
                'verificationUrl' => $this->verificationUrl,
                'hotel' => $hotelData,
                'rooms' => $dataReservation,
                'searchRequest' => json_decode($searchRequest, true),
            ]);
    }
}
