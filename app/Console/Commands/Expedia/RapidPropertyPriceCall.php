<?php

namespace App\Console\Commands\Expedia;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\API\Suppliers\ExpediaSupplier\PropertyCallFactory;

class RapidPropertyPriceCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:RapidPropertyPriceCall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RapidPropertyPriceCall';

    private PropertyCallFactory $rapidCallFactory;

    public function __construct(PropertyCallFactory $rapidCallFactory)
    {
        parent::__construct();
        $this->rapidCallFactory = $rapidCallFactory;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $start_time = microtime(true);

        $property['checkin'] = "2023-12-10";
        $property['checkout'] = "2023-12-31";
        $property['occupancy'][] = ["adults" => "2"];
        $propertyIds = ["12537922", "10231646", "10215116", "10630123", "10948924"];

        $propertyPriceCall = $this->rapidCallFactory->createPropertyPriceCall($property);

        $dataPrice = $propertyPriceCall->getPriceData($propertyIds);

        Log::debug('RapidPropertyPriceCall', ['value' => json_encode($dataPrice)]);

        $execution_time = (microtime(true) - $start_time);
        $this->info('Import completed. ' . round($execution_time, 2) . " seconds");
    }
}
