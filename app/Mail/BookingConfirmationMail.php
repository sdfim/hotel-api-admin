<?php

namespace App\Mail;

use App\Models\ApiBookingItem;
use App\Models\User;
use App\Repositories\ApiBookingInspectorRepository;
use App\Services\PdfGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
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
        $hotel = Hotel::where('giata_code', $giata_code)->first();

        $booking = ApiBookingInspectorRepository::getBookItemsByBookingItem($bookingItem->booking_item);
        $bookingMeta = $booking?->metadata ?? [];
        $requestBooking = json_decode($booking?->request ?? '', true) ?? [];
        $agentId = Arr::get($requestBooking, 'api_client.id');
        $agentEmail = Arr::get($requestBooking, 'api_client.email');
        $userAgent = User::where('id', $agentId)
            ->orWhere('email', $agentEmail)
            ->first();
        $clientLastNane = Arr::get($requestBooking, 'booking_contact.last_name');
        $clientFirstName = Arr::get($requestBooking, 'booking_contact.first_name');

        logger('BookingConfirmationMail', [
            'hotel' => $hotel,
            'booking' => $booking,
            'booking_request' => $requestBooking,
            'rooms' => $dataReservation,
            'searchRequest' => json_decode($searchRequest, true),
            'hotelPhotoPath' => $hotel?->product?->hero_image ? Storage::url($hotel?->product?->hero_image) : '',
        ]);
        $total_net = 0;
        $total_tax = 0;
        $total_fees = 0;
        $total_price = 0;

        foreach ($dataReservation as $item) {
            $total_net += $item['total_net'] ?? 0;
            $total_tax += $item['total_tax'] ?? 0;
            $total_fees += $item['total_fees'] ?? 0;
            $total_price += $item['total_price'] ?? 0;
        }

        // 🔹 генерируем PDF прямо здесь
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.booking-confirmation', [
            'customerName' => 'Mr '.$clientFirstName.' '.$clientLastNane,
            'hotel' => $hotel,
            'hotelData' => [
                'name' => $hotel?->product?->name ?? 'Unknown Hotel',
                'address' => trim(
                    ($hotel->address['line_1'] ?? '').', '.
                    ($hotel->address['city'] ?? '').', '.
                    ($hotel->address['state_province_name'] ?? '').', '.
                    ($hotel->address['country_code'] ?? '')
                ),
            ],
            'total_net' => $total_net,
            'total_tax' => $total_tax,
            'total_fees' => $total_fees,
            'total_price' => $total_price,
            'agency' => [
                'booking_agent' => $userAgent->name ?? 'KRISTINA SHACKNOW',
                'booking_agent_email' => $userAgent->email ?? 'kshacknow@ultimatejetvacations.com',
            ],
            'hotelPhotoPath' => $hotel?->product?->hero_image ? Storage::url($hotel?->product?->hero_image) : '',
        ]);

        return $this->subject('Your booking is confirmed')
            ->view('emails.booking.confirmation')
            ->with([
                'hotel' => $hotel,
                'bookingMeta' => $bookingMeta,
                'rooms' => $dataReservation,
                'searchRequest' => json_decode($searchRequest, true),
            ])
            ->attachData($pdfContent, 'BookingConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
