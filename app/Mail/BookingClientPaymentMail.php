<?php

namespace App\Mail;

use App\Models\ApiBookingsMetadata;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class BookingClientPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $payment_url;

    public $booking_id;

    public function __construct($payment_url, $booking_id)
    {
        $this->payment_url = $payment_url;
        $this->booking_id = $booking_id;
    }

    public function build()
    {
        $hotelName = null;
        $bookimgItemData = ApiBookingsMetadata::where('booking_id', $this->booking_id)->first()?->booking_item_data;

        logger()->info('BookApiHandler | book | bookimg_item_data', ['data' => $bookimgItemData, 'booking_id' => $this->booking_id]);

        if ($bookimgItemData) {
            $hotelName = Arr::get($bookimgItemData, 'hotel_name');
        }

        return $this->subject('Your booking is ready for payment')
            ->view('emails.booking.client_payment')
            ->with([
                'payment_url' => $this->payment_url,
                'hotelName' => $hotelName,
            ]);
    }
}
