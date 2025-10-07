<?php

namespace App\Mail;

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

class BookingQuoteVerificationMail extends Mailable implements ShouldQueue
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

        $hotel = $hotelData;

        $total_net = 0;
        $total_tax = 0;
        $total_fees = 0;
        $total_price = 0;
        $dataReservation = $service->getDataFirstSearch($bookingItem);
        foreach ($dataReservation as $item) {
            $total_net += $item['total_net'] ?? 0;
            $total_tax += $item['total_tax'] ?? 0;
            $total_fees += $item['total_fees'] ?? 0;
            $total_price += $item['total_price'] ?? 0;
        }

        logger('BookingQuoteVerificationMail', [
            'verificationUrl' => $this->verificationUrl,
            'denyUrl' => $this->denyUrl,
            'agentData' => $this->agentData,
            'hotel' => $hotelData,
            'rooms' => $dataReservation,
            'searchRequest' => json_decode($searchRequest, true),
        ]);

        // 🔹 генерируем PDF прямо здесь
        $pdfService = app(PdfGeneratorService::class);
        $pdfContent = $pdfService->generateRaw('pdf.quote-confirmation', [
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
                'booking_agent' => Arr::get($this->agentData, 'name') ?? 'KRISTINA SHACKNOW',
                'booking_agent_email' => Arr::get($this->agentData, 'email') ?? 'kshacknow@ultimatejetvacations.com',
            ],
            'hotelPhotoPath' => $hotel?->product?->hero_image ? Storage::url($hotel?->product?->hero_image) : '',
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
            ])
            ->attachData($pdfContent, 'BookingConfirmation.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
