<?php

namespace App\Console\Commands\Tools;

use App\Models\AirwallexApiLog;
use App\Models\ApiBookingPaymentInit;
use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class AirwallexSyncPaymentInits extends Command
{
    protected $signature = 'airwallex:sync-payment-inits';

    protected $description = 'Sync related_id and action in api_booking_payment_inits from airwallex_api_logs';

    public function handle()
    {
        $count = 0;
        $logs = AirwallexApiLog::orderBy('id')->get();
        foreach ($logs as $log) {
            $paymentIntentId = $log->payment_intent_id;
            $bookingId = $log->booking_id;
            $payload = ! is_array($log->payload) ? json_decode($log->payload, true) : $log->payload;
            $amount = Arr::get($payload, 'amount', 0);
            $currency = Arr::get($payload, 'currency', 'USD');
            if (! $bookingId) {
                continue;
            }
            $method = $log->method;
            $action = null;
            if ($method === 'createPaymentIntent') {
                $action = 'init';
            } elseif ($method === 'confirmationPaymentIntent') {
                $action = 'confirmed';
            }
            if ($action) {
                $init = ApiBookingPaymentInit::where('payment_intent_id', $paymentIntentId)
                    ->where('booking_id', $bookingId)
                    ->where('action', $action)
                    ->first();
                if ($init) {
                    $init->update([
                        'related_id' => $log->id,
                        'amount' => $amount,
                        'currency' => $currency,
                    ]);
                    $count++;
                } else {
                    ApiBookingPaymentInit::create([
                        'booking_id' => $bookingId,
                        'payment_intent_id' => $paymentIntentId,
                        'action' => $action,
                        'amount' => $amount,
                        'currency' => $currency,
                        'provider' => 'airwallex',
                        'related_id' => $log->id,
                        'related_type' => AirwallexApiLog::class,
                    ]);
                    $count++;
                }
            }
        }
        $this->info("Updated or created $count api_booking_payment_inits records.");

        // Delete ApiBookingPaymentInit records whose related_id does not exist in AirwallexApiLog
        $validRelatedIds = AirwallexApiLog::pluck('id')->toArray();
        $deleted = ApiBookingPaymentInit::whereNotIn('related_id', $validRelatedIds)->delete();
        $this->info("Deleted $deleted orphaned api_booking_payment_inits records.");
    }
}
