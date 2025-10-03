<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingClientPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $payment_url;

    public function __construct($payment_url)
    {
        $this->payment_url = $payment_url;
    }

    public function build()
    {
        return $this->subject('Your booking is ready for payment')
            ->view('emails.booking.client_payment')
            ->with([
                'payment_url' => $this->payment_url,
            ]);
    }
}
