<?php

namespace App\Mail;

use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\API\Services\HotelBookingCheckQuoteService;
use Modules\HotelContentRepository\Models\Hotel;

class BookingAgentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $bookingItem;

    public function __construct($bookingItem)
    {
        $this->bookingItem = $bookingItem;
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

        logger('BookingAgentNotificationMail', [
            'hotel' => $hotelData,
            'rooms' => $dataReservation,
            'searchRequest' => json_decode($searchRequest, true),
        ]);

        return $this->subject('Booking Confirmed by Client')
            ->view('emails.booking.agent_notification')
            ->with([
                'quoteNumber' => $quoteNumber,
                'hotel' => $hotelData,
                'rooms' => $dataReservation,
                'searchRequest' => json_decode($searchRequest, true),
            ]);
    }
}

