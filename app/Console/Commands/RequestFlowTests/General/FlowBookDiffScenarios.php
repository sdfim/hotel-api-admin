<?php

namespace App\Console\Commands\RequestFlowTests\General;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FlowBookDiffScenarios extends Command
{
    use FlowScenariosTrait;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flow:book-diff-scenarios {data?}';

    protected PendingRequest $client;

    protected string $url;

    protected string $api_client_id;

    protected bool $isQueueSync;

    public function __construct()
    {
        parent::__construct();
        $this->url = config('app.url', 'http://localhost');
        $this->isQueueSync = config('queue.default') === 'sync';
    }

    public function handle(): void
    {
        Artisan::call('cache:clear');

        $formData = $this->argument('data');

        $formData ?: $formData = [
            'type' => 'hotel',
            'supplier' => 'Oracle',
            'checkin' => '2026-05-05',
            'checkout' => '2026-05-13',
            'giata_ids' => [38049404],
            'occupancy' => [
                [
                    'adults' => 1,
                    'children_ages' => [],
                    'room_type' => null,
                    'rate_plan_code' => null,
                    'meal_plan_code' => null,
                ],
            ],
            'blueprint_exist' => false,
        ];

        $formData = $this->removeEmptyValues($formData);

        $run_booking_flow = $formData['run_booking_flow'] ?? true;
        $run_cancellation_flow = $formData['run_cancellation_flow'] ?? true;

        $key_rs_cache = $formData['key_rs_cache'] ?? null;

        $api_user_email = $formData['api_user'] ?? null;
        $api_user = User::where('email', $api_user_email)->first();
        $token = $api_user ? $api_user->channel->access_token : env('TEST_TOKEN');
        $this->api_client_id = $api_user ? $api_user->id : 19;
        unset($formData['run_booking_flow'], $formData['run_cancellation_flow'], $formData['api_user']);

        $this->client = Http::withToken($token);

        // Search
        $searchResponse = $this->search($formData);

        // Find booking item
        $bookingItem = $this->fetchBookingItem($searchResponse);

        if (! $bookingItem) {
            $this->error('Booking item not found by given room params');
            exit(1);
        }

        // Add to booking
        $this->handleSleep();
        $bookingId = $this->addBookingItem($bookingItem);

        // Add Passengers
        $this->handleSleep();
        $responseAddPassengers = $this->addPassengers($bookingId, [$bookingItem], [$formData['occupancy']]);
        if (Arr::get($responseAddPassengers, 'error')) {
            $this->error('Adding passengers failed');
            exit(1);
        }

        $this->handleSleep();

        // Book
        $run_booking_flow ?? $this->book($bookingId, $bookingItem);
        sleep(5);

        // Cancel
        $run_cancellation_flow && $run_booking_flow ?? $this->cancel($bookingId, $bookingItem);

        // Store result in cache
        if ($key_rs_cache) {
            $dataForQueue = [
                'search_id' => $this->search_id,
                'booking_id' => $bookingId,
                'booking_item' => $bookingItem,
            ];
            Cache::put($key_rs_cache, $dataForQueue, 3600);
        }
    }

    public function removeEmptyValues(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->removeEmptyValues($value);

                // Remove the key if the filtered array is empty
                if (empty($array[$key])) {
                    unset($array[$key]);
                }
            } elseif (is_null($value) || $value === '' || $value === []) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}
