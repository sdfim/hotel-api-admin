<?php

namespace App\Mail;

use App\Models\ApiBookingsMetadata;
use App\Services\BookingEmailDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class BookingClientPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public ?string $payment_url;

    public ?string $booking_id;

    public ?string $booking_item;

    public function __construct(string $payment_url, string $booking_id, ?string $booking_item = null)
    {
        $this->payment_url = $payment_url;
        $this->booking_id = $booking_id;
        $this->booking_item = $booking_item;
    }

    public function build()
    {
        $hotelName = 'your hotel';
        $data = [];

        if ($this->booking_item) {
            /** @var BookingEmailDataService $dataService */
            $dataService = app(BookingEmailDataService::class);
            $data = $dataService->getBookingData($this->booking_item);
            $hotelName = $data['hotelName'];
        }

        if ($this->booking_id && $hotelName === 'your hotel') {
            $bookimgMetadata = ApiBookingsMetadata::where('booking_id', $this->booking_id)->first();
            $bookimgItemData = $bookimgMetadata?->booking_item_data;

            if ($bookimgItemData) {
                $hotelName = Arr::get($bookimgItemData, 'hotel_name', 'your hotel');
            }
        }

        $appName = env('APP_NAME', '');

        return $this->subject("Your $appName Advisor has booked you at $hotelName and your booking is ready for payment")
            ->view('emails.booking.client_payment')
            ->with(array_merge($data, [
                'payment_url' => $this->payment_url,
                'hotelName' => $hotelName,
            ]));
    }
}
