<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Repositories\ApiBookingItemRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\API\Payment\Controllers\PaymentController;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CloseRemainingBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:close-remaining-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close remaining balance for active reservations';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to process reservations for remaining balance closure...');

        // Retrieve active reservations where canceled_at is null and total_cost > paid
        $reservations = Reservation::whereNull('canceled_at')
            ->whereColumn('total_cost', '>', 'paid')
            ->get();

        foreach ($reservations as $reservation) {
            try {
                $this->info("Processing reservation ID: {$reservation->id}");

                // Calculate remaining deposit term (dummy logic for now)
                if (($reservation->total_cost - $reservation->paid) <= 0) {
                    $this->info("No remaining balance for reservation ID: {$reservation->id}");

                    continue;
                }

                $remainingBalance = ApiBookingItemRepository::getDepositData($reservation->booking_id);
                foreach ($remainingBalance as $balance) {
                    if (Arr::get($balance, 'due_date') != now()->toDateString()) {
                        $this->info("Skipping payment for reservation ID: {$reservation->id} as due date is not today. Due date: ".Arr::get($balance, 'due_date'));

                        continue;
                    }
                    $controller = app(PaymentController::class);
                    $controller->createPaymentIntentMoFoF($reservation->booking_id, Arr::get($balance, 'total_deposit'));
                }

                $this->info("Payment processed successfully for reservation ID: {$reservation->id}");
            } catch (\Exception $e) {
                Log::error("Failed to process reservation ID: {$reservation->id}", [
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed to process reservation ID: {$reservation->id}. Check logs for details.");
            }
        }

        $this->info('Finished processing reservations.');

        return CommandAlias::SUCCESS;
    }
}
