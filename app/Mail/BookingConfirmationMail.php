<?php

namespace App\Mail;

use App\Models\ApiBookingItem;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\API\Services\HotelBookingCheckQuoteService;
use Modules\HotelContentRepository\Models\Hotel;

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
        $bookingItem = ApiBookingItem::where('booking_item', $this->bookingItem)->first();
        $service = app(HotelBookingCheckQuoteService::class);
        $dataReservation = $service->getDataFirstSearch($bookingItem);
        $searchRequest = $bookingItem->search->request;
        $giata_code = $dataReservation[0]['giata_code'] ?? null;
        $hotelData = Hotel::where('giata_code', $giata_code)->first();

        $bookingId = ApiBookingInspectorRepository::getBookItemsByBookingItem($bookingItem->booking_item);
        $bookingMeta = $bookingId?->metadata ?? [];

        logger('BookingConfirmationMail', [
            'hotel' => $hotelData,
            'rooms' => $dataReservation,
            'searchRequest' => json_decode($searchRequest, true),
        ]);

        return $this->subject('Your booking is confirmed')
            ->view('emails.booking.confirmation')
            ->with([
                'hotel' => $hotelData,
                'bookingMeta' => $bookingMeta,
                'rooms' => $dataReservation,
                'searchRequest' => json_decode($searchRequest, true),
            ]);
    }
}
