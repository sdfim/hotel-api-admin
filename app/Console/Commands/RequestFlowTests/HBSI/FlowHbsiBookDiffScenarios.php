<?php

namespace App\Console\Commands\RequestFlowTests\HBSI;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class FlowHbsiBookDiffScenarios extends Command
{
    use FlowHbsiScenariosTrait;

    protected $signature = 'flow:hbsi-book-diff-scenarios {scenarios?} {destination?} {checkin?} {giata_id?}';

    protected PendingRequest $client;

    protected string $url;

    private ?string $destination;

    private ?string $checkin;

    private ?int $giata_id;

    private ?string $supplier;

    private ?int $daysAfter;

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
                'scenario_1',
                'scenario_2',
                'scenario_3',
                'scenario_4',
                'scenario_5',
                'scenario_6',
                'scenario_7',
            ];

        $this->runScenarios($scenariosToRun);

        /**
         * #########################
         * Scenario #1
         *
         * Book Room Only with 2 Adults for 5 days for Initial test
         * Modify Reservation from Scenario #1 and Change the Arrival Date
         * Verify the ReadRQ return the booking Details
         * Cancel Reservation from Scenario #1
         * 51721;Aug 5 - 10
         * BAR/Double
         *
         * #########################
         * Scenario #2
         *
         * Book Room Only with 2 Adult for 5 nights
         * Cancel Reservation from above scenario #2
         * 51721;Aug 5 - 10
         * Promo/Double
         *
         * #########################
         * Scenario #3
         *
         * Book Room Only with 2 Adults, 1 Child, 1 Teen, and 1 Infant for two rooms for 2 days
         * Verify rates by person if policy is applied  (This Scenario of Child, Teen and/or Infant only apply if Partner supports these age categories)
         * Cancel Reservation from above scenario #3
         * 51721;Aug 15 - 17
         * BAR/Suite
         *
         * #########################
         * Scenario #4
         *
         * Book Room Only with 2 Adults with Comments and/or Special Requests (if Partner Supports)
         * Cancel Reservation from the above Scenario #4
         * Use 51721
         *
         * #########################
         * Scenario #5
         *
         * Book 2 rooms with 2 different room types 1 adult and 1 child in each room
         * Cancel Reservation from the above Scenario #5
         * 51721;Book Double and Suite
         *
         * #########################
         * Scenario #6
         *
         * Book 2 rooms with 2 different rate plans 1 adult and 1 child in each room
         * Cancel Reservation from the above Scenario #6
         * 51721;Book BAR and Promo
         *
         * #########################
         * Scenario #7
         *
         * Partial Cancellation in multi room booking
         * Cancel Reservation from above scenario #7 only one room
         *
         *  #########################
         *  Scenario #8
         *
         * Book Room for 2 Adults with included mealplan as All inclusive
         * 51721; Best /Suite
         * Aug 25-28
         *
         * #########################
         *   Scenario #9
         *
         * Book Room with 1 Adults and one Child to tested with additional mealplan "Breakfast" with additional rate
         * 51721;BAR /Suite
         * Aug 25-28
         */
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
        $occupancy = [['adults' => 2]];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'Loyalty',
                'room_type' => 'Luxury',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        $checkin = Carbon::parse($checkin)->addDays(1)->toDateString();
        $this->flowHardChange($bookingId, $bookingItem, $occupancy, $checkin, $checkout);

        $this->cancel($bookingId);

        sleep(2);

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
                'rate_name' => 'Promo',
                'room_type' => 'Double',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        // $this->cancel($bookingId);
    }

    private function scenario_3(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #3');
        // Book Room Only with 2 Adults, 1 Child, 1 Teen, and 1 Infant for two rooms for 2 days
        $occupancy = [
            ['adults' => 2, 'children_ages' => [5, 1]], // 5: child, 1: infant
        ];
        $nights = 2;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'BAR',
                'room_type' => 'Suite',
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

        // $this->cancel($bookingId);
    }

    private function scenario_5(): void
    {
        $this->info('------------------------------------');
        $this->warn('Starting Scenario #5');
        $occupancy = [
            ['adults' => 1, 'children_ages' => [1]],
            ['adults' => 1, 'children_ages' => [1]],
        ];
        $nights = 5;
        $checkin = $this->checkin;
        $checkout = Carbon::parse($checkin)->addDays($nights)->toDateString();

        $options = [
            [
                'rate_name' => 'BAR',
                'room_type' => 'Double',
            ],
            [
                'rate_name' => 'BAR',
                'room_type' => 'Suite',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        // $this->cancel($bookingId);
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
                'rate_name' => 'BAR',
            ],
            [
                'rate_name' => 'Promo',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);
        // $this->cancel($bookingId);
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

        // $this->cancel($bookingId, $bookingItem);
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
                'rate_name' => 'Best',
                'room_type' => 'Suite',
                'meal_plan' => 'All Inclusive',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        // $this->cancel($bookingId, $bookingItem);
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
                'rate_name' => 'BAR',
                'room_type' => 'Suite',
                'meal_plan' => 'Breakfast',
            ],
        ];

        [$bookingId, $bookingItem] = $this->processBooking($occupancy, $checkin, $checkout, $options);

        // $this->cancel($bookingId, $bookingItem);
    }


    public function preset(): void
    {
        $this->destination = $this->argument('destination') ?? '508';
        $this->checkin = $this->argument('checkin') ?? null;
        $this->giata_id = $this->argument('giata_id') ?? null;
        $this->supplier = 'HBSI';
        $this->daysAfter = $this->checkin ? (abs(Carbon::parse($this->checkin)->diffInDays(Carbon::now())) + 20) : 240;
    }
}
