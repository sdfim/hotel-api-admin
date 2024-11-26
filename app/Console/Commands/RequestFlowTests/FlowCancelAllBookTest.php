<?php

namespace App\Console\Commands\RequestFlowTests;

use App\Repositories\ApiBookingInspectorRepository;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class FlowCancelAllBookTest extends Command
{
    protected $signature = 'flow:cancel-all-book-test {supplierName}';
    protected $description = 'Cancel all test bookings for supplier (HBSI, Expedia, etc)';

    protected PendingRequest $client;
    protected string $url;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'))->timeout(60);
        $this->url = env('BASE_URI_FLOW_HBSI_BOOK_TEST', 'http://localhost:8000');
    }

    public function handle()
    {
        $supplierName = $this->argument('supplierName');
        $bookings = ApiBookingInspectorRepository::getAllBookTestForCancel($supplierName);

        foreach ($bookings as $booking) {
            $requestData = [
                'booking_id' => $booking->booking_id,
                'booking_item' => $booking->booking_item,
            ];

            $response = $this->client->delete($this->url . '/api/booking/cancel-booking', $requestData);

            if ($response->successful()) {
                $this->info('Cancelled booking: ' . $booking->booking_id . ' with booking item: ' . $booking->booking_item);
            } else {
                $this->error('Failed to cancel booking: ' . $booking->booking_id . ' with booking item: ' . $booking->booking_item);
            }
        }
    }
}
