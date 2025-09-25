<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BookingEmailVerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $verificationUrl;
    public $bookingItem;

    /**
     * Create a new message instance.
     */
    public function __construct($verificationUrl, $bookingItem)
    {
        $this->verificationUrl = $verificationUrl;
        $this->bookingItem = $bookingItem;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Подтвердите бронирование',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.booking.email_verification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject('Подтвердите бронирование')
            ->markdown('emails.booking.email_verification')
            ->with([
                'verificationUrl' => $this->verificationUrl,
                'bookingItem' => $this->bookingItem,
            ]);
    }
}
