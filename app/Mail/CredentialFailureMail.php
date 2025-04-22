<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredentialFailureMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $provider, public string $errorMessage){}

    public function build()
    {
        return $this->subject("[" . strtoupper($this->provider) . "] Credential Failure in OBE")
                    ->view('emails.credential-failure')
                    ->with([
                        'provider' => $this->provider,
                        'error' => $this->errorMessage,
                        'timestamp' => now()->toDateTimeString()
                    ]);
    }

    
}
