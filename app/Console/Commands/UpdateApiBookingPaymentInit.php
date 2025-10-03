<?php

namespace App\Console\Commands;

use App\Models\AirwallexApiLog;
use App\Models\ApiBookingPaymentInit;
use App\Models\Enums\PaymentStatusEnum;
use Illuminate\Console\Command;

class UpdateApiBookingPaymentInit extends Command
{
    protected $signature = 'airwallex:update-api-booking-payment-init';

    protected $description = 'Create or update ApiBookingPaymentInit records for all AirwallexApiLog entries.';

    public function handle()
    {
        $logs = AirwallexApiLog::all();
        $count = 0;
        foreach ($logs as $log) {
            $payload = $log->payload ?? [];
            $response = $log->response ?? [];
            $amount = $payload['amount'] ?? $response['amount'] ?? null;
            $currency = $payload['currency'] ?? $response['currency'] ?? null;

            if (! $log->booking_id || ! $amount || ! $currency) {
                continue;
            }

            // Determine action based on method
            if ($log->method === 'confirmationPaymentIntent') {
                $action = PaymentStatusEnum::CONFIRMED->value;
            } elseif ($log->method === 'createPaymentIntent') {
                $action = PaymentStatusEnum::INIT->value;
            } else {
                continue; // Skip other methods
            }

            ApiBookingPaymentInit::updateOrCreate([
                'booking_id' => $log->booking_id,
                'payment_intent_id' => $log->payment_intent_id,
                'provider' => 'airwallex',
                'related_id' => $log->id,
                'related_type' => AirwallexApiLog::class,
                'action' => $action,
            ], [
                'amount' => $amount,
                'currency' => $currency,
            ]);
            $count++;
        }
        $this->info("Processed $count AirwallexApiLog records.");
    }
}
