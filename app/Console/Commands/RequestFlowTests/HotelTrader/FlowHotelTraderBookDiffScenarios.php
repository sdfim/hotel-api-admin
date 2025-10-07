<?php

namespace App\Console\Commands\RequestFlowTests\HotelTrader;

use App\Repositories\ApiBookingItemRepository;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class FlowHotelTraderBookDiffScenarios extends Command
{
    use FlowHotelTraderScenariosTrait;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'flow:htrader-book-diff-scenarios {scenarios?} {checkin?} {giata_id?}';

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
                'scenario_s3',
                'scenario_s4',
                'scenario_s5',
                'scenario_s6',
                'scenario_s7',
                'scenario_s8',

                'scenario_1',
                'scenario_2',
                'scenario_3',
                'scenario_4',
                'scenario_5',
                'scenario_6',
                'scenario_7',
                'scenario_8',
                'scenario_9',
                'scenario_10',
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

    private function scenario_1(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #1');
        $occupancy = [['adults' => 2], ['adults' => 1, 'children_ages' => [5]]];
        $nights = 1;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                //                'rate_name' => 'HTPKG1',
                'room_type' => 'SKVA',
                'non_refundable' => false,
                'supplier_room_id' => 1,
            ],
            [
                //                                'rate_name' => 'HTOPQR',
                'room_type' => 'S1KV',
                'non_refundable' => false,
                'supplier_room_id' => 2,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $checkout = Carbon::parse($checkout)->addDays(1)->toDateString();
        $this->flowHardChange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);

        $this->cancel($bookingId, $bookingItem);

        $this->retrieveBooking($bookingId);
    }

    private function scenario_10(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #1');
        $occupancy = [['adults' => 2]];
        $nights = 1;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                //                'rate_name' => 'HTPKG1',
                'room_type' => 'SKVA',
                'non_refundable' => false,
                'supplier_room_id' => 1,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $checkout = Carbon::parse($checkout)->addDays(1)->toDateString();
        $this->flowHardChange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);

        $this->cancel($bookingId, $bookingItem);

        $this->retrieveBooking($bookingId);
    }

    private function scenario_2(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #2');
        $occupancy = [['adults' => 2]];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'HTREN3',
                'room_type' => 'DLX0001K',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        //
        //        $this->cancel($bookingId, $bookingItem);
        //
        //        $this->retrieveBooking($bookingId);
    }

    private function scenario_3(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #3');
        // Book Room Only with 2 Adults and 1 Child, 1 Teen, and 1 Infant for two rooms for 2 days
        $occupancy = [
            ['adults' => 1, 'children_ages' => [5, 1, 12]],
            ['adults' => 3],
        ];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => false,
                'supplier_room_id' => 1,
            ],
            [
                'non_refundable' => false,
                'supplier_room_id' => 2,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        // $this->cancel($bookingId);
    }

    private function scenario_4(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #4');
        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout);

        $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_5(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #5');
        $occupancy = [
            ['adults' => 1, 'children_ages' => [1]],
            ['adults' => 2],
        ];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                //                'rate_name' => 'HTRET',
                'room_type' => 'STD0002D',
                'non_refundable' => false,
            ],
            [
                //                'rate_name' => 'HTRETN',
                'room_type' => 'STDAS01K',
                'non_refundable' => false,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId, $bookingItem);

        $this->retrieveBooking($bookingId);
    }

    private function scenario_6(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #6');
        $occupancy = [
            ['adults' => 1, 'children_ages' => [1]],
            ['adults' => 1, 'children_ages' => [1]],
        ];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                //                'rate_name' => 'HTRETN',
                //                'room_type' => 'STD0002D',
                'non_refundable' => false,
                'supplier_room_id' => 1,
            ],
            [
                //                'rate_name' => 'HTPKG3',
                //                'room_type' => 'STDAS01K',
                'non_refundable' => false,
                'supplier_room_id' => 2,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId, $bookingItem);

        $this->retrieveBooking($bookingId);
    }

    private function scenario_7(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #7');
        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout);

        $this->info('------------------------------------');

        $occupancy2 = [['adults' => 1, 'children_ages' => [5]]];
        $checkin2 = Carbon::now()->addDays($this->daysAfter)->toDateString();
        $checkout2 = Carbon::now()->addDays($this->daysAfter + $nights)->toDateString();

        [$bookingId, $bookingItem] = $this->processBooking($occupancy2, $checkin2, $checkout2, [], null, $bookingId);

        $this->cancel($bookingId, $bookingItem);

        $this->retrieveBooking($bookingId);
    }

    private function scenario_8(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #8');
        $occupancy = [['adults' => 2]];
        $nights = 3;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'HTREN3',
                'room_type' => 'DLX0001K',
                'meal_plan' => 'Free Continental Breakfast',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId, $bookingItem);

        $this->retrieveBooking($bookingId);
    }

    private function scenario_9(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #9');
        $occupancy = [['adults' => 1, 'children_ages' => [5]]];
        $nights = 3;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'HTRET',
                'room_type' => 'SUP0002D',
                'meal_plan' => 'Free Breakfastt',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $this->cancel($bookingId, $bookingItem);

        $this->retrieveBooking($bookingId);
    }

    // SERTIFICATION SCENARIOS

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

        //        $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_s2(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s2');
        $occupancy = [['adults' => 2]];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => true,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

                $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_s3(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s3');
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

        //        $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_s4(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s4');
        $occupancy = [['adults' => 2, 'children_ages' => [5]]];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => true,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

//        $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_s5(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s5');
        $occupancy = [['adults' => 2], ['adults' => 2]];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => false,
                'supplier_room_id' => 1,
            ],
            [
                'non_refundable' => false,
                'supplier_room_id' => 2,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        //        $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_s6(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s6');
        $occupancy = [['adults' => 2], ['adults' => 2]];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => true,
                'supplier_room_id' => 1,
            ],
            [
                'non_refundable' => true,
                'supplier_room_id' => 2,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        //        $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_s7(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s7');
        $occupancy = [['adults' => 2, 'children_ages' => [5]], ['adults' => 2, 'children_ages' => [5]]];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => false,
                'supplier_room_id' => 1,
            ],
            [
                'non_refundable' => false,
                'supplier_room_id' => 2,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        //                $this->cancel($bookingId, $bookingItem);
    }

    private function scenario_s8(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #s8');
        $occupancy = [['adults' => 2, 'children_ages' => [5]], ['adults' => 2, 'children_ages' => [5]]];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'non_refundable' => true,
                'supplier_room_id' => 1,
            ],
            [
                'non_refundable' => true,
                'supplier_room_id' => 2,
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        //        $this->cancel($bookingId, $bookingItem);
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
