<?php

namespace App\Console\Commands\Tools;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AirwallexSyncPaymentInits extends Command
{
    protected $signature = 'airwallex:sync-payment-inits';
    protected $description = 'Sync related_id and action in api_booking_payment_inits from airwallex_api_logs';

    public function handle()
    {
        $count = 0;
        DB::table('airwallex_api_logs')->orderBy('id')->chunk(100, function ($logs) use (&$count) {
            foreach ($logs as $log) {
                $paymentIntentId = $log->payment_intent_id;
                $bookingId = $log->booking_id;
                $method = $log->method;
                $action = null;
                if ($method === 'createPaymentIntent') {
                    $action = 'init';
                } elseif ($method === 'confirmationPaymentIntent') {
                    $action = 'confirmed';
                }
                if ($action) {
                    $updated = DB::table('api_booking_payment_inits')
                        ->where('payment_intent_id', $paymentIntentId)
                        ->where('booking_id', $bookingId)
                        ->update([
                            'related_id' => $log->id,
                            'action' => $action,
                        ]);
                    if ($updated) {
                        $count++;
                    }
                }
            }
        });
        $this->info("Updated $count api_booking_payment_inits records.");
    }
}

