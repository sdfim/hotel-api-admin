<?php

namespace App\Mail;

use App\Models\User;
use App\Repositories\ApiBookingInspectorRepository;
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

    public $denyUrl;

    public $bookingItem;

    public $agentData;

    public function __construct($verificationUrl, $denyUrl, $bookingItem, $agentData)
    {
        $this->verificationUrl = $verificationUrl;
        $this->denyUrl = $denyUrl;
        $this->bookingItem = $bookingItem;
        $this->agentData = $agentData;
    }

    public function build()
    {
        $bookingItem = \App\Models\ApiBookingItem::where('booking_item', $this->bookingItem)->first();
//        $quoteNumber = $bookingItem->booking_item ?? 'N/A';
        $quoteNumber = ApiBookingInspectorRepository::getBookIdByBookingItem($bookingItem->booking_item);
        $service = app(HotelBookingCheckQuoteService::class);
        $dataReservation = $service->getDataFirstSearch($bookingItem);
        $searchRequest = $bookingItem->search->request;
        $giata_code = $dataReservation[0]['giata_code'] ?? null;
        $hotelData = Hotel::where('giata_code', $giata_code)->first();
        $user = User::where('email', $this->agentData['email'])->first();
        $this->agentData['name'] = $user ? $user->name : 'N/A';

        logger('BookingEmailVerificationMail', [
            'verificationUrl' => $this->verificationUrl,
            'denyUrl' => $this->denyUrl,
            'agentData' => $this->agentData,
            'hotel' => $hotelData,
            'rooms' => $dataReservation,
            'searchRequest' => json_decode($searchRequest, true),
        ]);

        return $this->subject('Confirm your booking')
            ->view('emails.booking.email_verification')
            ->with([
                'verificationUrl' => $this->verificationUrl,
                'denyUrl' => $this->denyUrl,
                'agentData' => $this->agentData,
                'quoteNumber' => $quoteNumber,
                'hotel' => $hotelData,
                'rooms' => $dataReservation,
                'searchRequest' => json_decode($searchRequest, true),
            ]);
    }
}
