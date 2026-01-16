<?php

namespace App\Console\Commands\RequestFlowTests\General;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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

    public function handle(): int
    {
        Artisan::call('cache:clear');

        $formData = $this->argument('data');

        $formData ?: $formData = [
            'type' => 'hotel',
            'currency' => '*',
            'supplier' => 'Oracle',
            'checkin' => Carbon::now()->addMonths(3)->toDateString(),
            'checkout' => Carbon::now()->addMonths(3)->addDays(2)->toDateString(),
            'giata_ids' => [38049404],
            'occupancy' => [
                [
                    'adults' => 1,
                    'children_ages' => [],
                    'room_type' => 'LCOST',
                    'rate_plan_code' => 'CLIENTGL',
                    'meal_plan_code' => null,
                    'special_request' => 'High floor, non-smoking',
                    'comment' => 'Please provide extra pillows',
                ],
                [
                    'adults' => 1,
                    'children_ages' => [],
                    'room_type' => 'GMAMR',
                    'rate_plan_code' => 'CLIENTGM',
                    'meal_plan_code' => null,
                    'special_request' => 'Near elevator, non-smoking',
                    'comment' => 'Late check-in requested',
                ],
            ],
            'blueprint_exist' => false,
            'run_booking_flow' => true,
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
        $bookingItem = $this->fetchBookingItem($searchResponse, $formData['occupancy']);

        if (! $bookingItem) {
            logger()->error('FlowBookDiffScenarios _ Booking item not found by given room params', ['formData' => $formData, 'searchResponse' => $searchResponse]);

            return $this->reportErrorAndFail('Booking item not found by given room params', $key_rs_cache);
        }

        // Disable email sending for this booking item. Running test flow only
        Cache::put('bookingItem_no_mail_'.$bookingItem, false, 600);

        if (! $bookingItem) {
            return $this->reportErrorAndFail('Booking item not found by given room params (secondary check)', $key_rs_cache);
        }

        // Add to booking
        $this->handleSleep();
        $bookingId = $this->addBookingItem($bookingItem);

        if (! $bookingId) {
            return $this->reportErrorAndFail('Adding item to booking failed', $key_rs_cache);
        }

        // Add Passengers
        $this->handleSleep();
        $responseAddPassengers = $this->addPassengers($bookingId, [$bookingItem], [$formData['occupancy']]);
        if (Arr::get($responseAddPassengers, 'error')) {
            return $this->reportErrorAndFail('Adding passengers failed', $key_rs_cache);
        }

        $this->handleSleep();

        // Book
        if ($run_booking_flow) {
            $this->book($bookingId, [$bookingItem], $formData);
        }

        $this->handleSleep();

        // Cancel
        if ($run_cancellation_flow && $run_booking_flow) {
            $this->cancel($bookingId);
        }

        // Store result in cache
        if ($key_rs_cache) {
            $dataForQueue = [
                'search_id' => $this->search_id,
                'booking_id' => $bookingId,
                'booking_item' => $bookingItem,
            ];
            Cache::put($key_rs_cache, $dataForQueue, 3600);
        }

        return self::SUCCESS;
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

    private function reportErrorAndFail(string $message, ?string $cacheKey): int
    {
        $this->error($message);

        if ($cacheKey) {
            Cache::put($cacheKey, ['error' => $message], 3600);
        }

        return self::FAILURE;
    }
}
