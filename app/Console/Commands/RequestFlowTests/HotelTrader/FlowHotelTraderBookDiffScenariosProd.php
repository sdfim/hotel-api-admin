<?php

namespace App\Console\Commands\RequestFlowTests\HotelTrader;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class FlowHotelTraderBookDiffScenariosProd extends Command
{
    use FlowHotelTraderScenariosTrait;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flow:htrader-book-diff-scenarios-prod {scenarios?} {checkin?} {giata_id?}';

    protected PendingRequest $client;

    protected string $url;

    private ?string $checkin;

    private ?int $giata_id;

    private ?string $supplier;

    private ?int $daysAfter;

    protected bool $isQueueSync;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'));
        $this->url = env('BASE_URI_FLOW_HOTEL_TRADER_BOOK_TEST', 'http://localhost');
        $this->isQueueSync = config('queue.default') === 'sync';
    }

    public function handle(): void
    {
        $this->preset();
        Artisan::call('cache:clear');

        $scenariosToRun = $this->argument('scenarios')
            ? array_map('trim', explode(',', $this->argument('scenarios')))
            : [
                'scenario_s1',
                'scenario_s2',
            ];

        $this->runScenarios($scenariosToRun);
    }

    private function runScenarios(array $scenarios): void
    {
        foreach ($scenarios as $scenario) {
            $methodName = 'scenario_'.$scenario;
            if (method_exists($this, $methodName)) {
                $this->$methodName();
            } else {
                $this->warn("Scenario method $methodName does not exist.");
            }
        }
    }

    private function scenario_s1(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s1');
        $occupancy = [['adults' => 2]];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => false,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_s2(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s2');
        $occupancy = [['adults' => 2, 'children_ages' => [5]]];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => false,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId, $bookingItem);
    }

    // ######### additional methods ##########
    private function preset(): void
    {
        $this->checkin = $this->argument('checkin') ?? null;
        $this->giata_id = $this->argument('giata_id') ?? null;
        $this->supplier = 'HotelTrader';
        $this->daysAfter = $this->checkin ? (abs(Carbon::parse($this->checkin)->diffInDays(Carbon::now())) + 20) : 240;
    }
}
