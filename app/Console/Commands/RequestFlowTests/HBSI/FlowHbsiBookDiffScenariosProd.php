<?php

namespace App\Console\Commands\RequestFlowTests\HBSI;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Modules\Enums\SupplierNameEnum;

class FlowHbsiBookDiffScenariosProd extends Command
{
    use FlowHbsiScenariosTrait;

    protected $signature = 'flow:hbsi-book-diff-scenarios-prod {scenarios?} {checkin?} {giata_id?}';

    protected PendingRequest $client;

    private ?string $checkin;

    private ?int $giata_id;

    private string $supplier;

    protected string $url;

    protected bool $isQueueSync;

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(env('TEST_TOKEN'));
        $this->url = env('BASE_URI_FLOW_HBSI_BOOK_TEST', 'http://localhost:8000');
        $this->isQueueSync = config('queue.default') === 'sync';
    }

    public function handle(): void
    {
        $this->preset();
        Artisan::call('cache:clear');

        $scenariosToRun = $this->argument('scenarios')
            ? array_map('trim', explode(',', $this->argument('scenarios')))
            : [
                'scenario_10',
                'scenario_1',
                'scenario_2',
                'scenario_3',
                'scenario_4',
                'scenario_5',
                'scenario_6',
                'scenario_33',
                'scenario_7',
                'scenario_61',
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

    // Scenario 4: Zen Family Suite, 4 adults, 1 child (16), 1 infant, Jan 8-12, 2026
    private function scenario_10(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #10');

        $occupancy = [['adults' => 2]];
        $checkin = $this->checkin;
        $nights = 4;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'non_refundable' => false,
//                'rate_plan_code' => 'FORASPEEP',
//                'room_type' => 'STE1K',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    private function scenario_1(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #1');

        $occupancy = [['adults' => 2]];
        $checkin = $this->checkin;
        $nights = 4;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'GCL',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    // Scenario 2: Ambassador Pool suite, 2 adults, Nov 22-26, 2025
    private function scenario_2(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #2');

        $occupancy = [['adults' => 2]];
        $checkin = $this->checkin;
        $nights = 4;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'AMBPOOL',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    // Scenario 3: Ambassador Suite Ocean View, 4 adults, Feb 5-10, 2026, occupancy restriction test
    private function scenario_3(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #3');

        $occupancy = [['adults' => 4]];
        $checkin = $this->checkin;
        $nights = 5;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'AMB',
                'comment' => 'occupancy restriction test',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    // Scenario 4: Zen Family Suite, 4 adults, 1 child (16), 1 infant, Jan 8-12, 2026
    private function scenario_4(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #4');

        $occupancy = [['adults' => 4, 'children_ages' => [16, 1]]];
        $checkin = $this->checkin;
        $nights = 4;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'ZENFAMILY',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    // Scenario 5: Zen Pool, 1 adult, Jan 8-12, 2026
    private function scenario_5(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #5');

        $occupancy = [['adults' => 1]];
        $checkin = $this->checkin;
        $nights = 4;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'ZENPOOL',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    // Scenario 6: Zen Pool, 3 adults, Jan 8-12, 2026
    private function scenario_6(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #6');

        $occupancy = [['adults' => 3]];
        $checkin = $this->checkin;
        $nights = 4;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'ZENPOOL',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    // Scenario 61: Zen Pool, 2 adults, Jan 8-12, 2026, change check-in date to Jan 9
    private function scenario_61(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #61');

        $occupancy = [['adults' => 2]];
        $checkin = $this->checkin;
        $nights = 4;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'ZENPOOL',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $checkin = Carbon::parse($checkin)->addDays(1)->toDateString();

        //        $bookingId = '';
        //        $bookingItem = '';
        $roomType = 'ZEN';
        $this->flowHardChange($bookingId, $bookingItem, $occupancy, $checkin, $checkout, $roomType);

        $this->cancel($bookingId);
    }

    private function scenario_33(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #3');

        $occupancy = [['adults' => 2]];
        $checkin = $this->checkin;
        $nights = 5;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'AMB',
                'comment' => 'occupancy restriction test',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    private function scenario_7(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #7');

        $occupancy = [
            ['adults' => 4, 'children_ages' => [16, 1]],
            ['adults' => 1],
        ];
        $checkin = $this->checkin;
        $nights = 4;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();
        $options = [
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'ZENFAMILY',
            ],
            [
                'rate_plan_code' => 'RO2',
                'room_type' => 'ZENPOOL',
            ],
        ];
        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId);
    }

    private function preset(): void
    {
        $this->checkin = $this->argument('checkin') ?? null;
        $this->giata_id = $this->argument('giata_id') ?? null;
        $this->supplier = SupplierNameEnum::HBSI->value;
    }
}
