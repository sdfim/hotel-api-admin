<?php

namespace App\Jobs;

use App\Mail\CredentialFailureMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailCredentialsAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $provider;

    protected string $error;

    public function __construct(string $provider, string $error)
    {
        $this->provider = $provider;
        $this->error = $error;
    }

    public function handle(): void
    {
        $cacheKey = "credential_alert_{$this->provider}_cooldown";
        $cooldownMinutes = config('alerts.credential.cooldown');
        $recipient = config('alerts.credential.email.to');

        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            Mail::to($recipient)
                ->send(new CredentialFailureMail($this->provider, $this->error));
            Cache::put($cacheKey, true, now()->addMinutes($cooldownMinutes));
        } catch (\Exception $e) {
            Log::error("Failed to send credential alert for {$this->provider}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
