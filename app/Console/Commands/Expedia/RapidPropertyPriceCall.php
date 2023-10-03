<?php

namespace App\Console\Commands\Expedia;

use Illuminate\Console\Command;
use Modules\API\Suppliers\ExpediaSupplier\PropertyPriceCall;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Illuminate\Support\Facades\Cache;

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

    /**
     * Execute the console command.
     */
    public function handle ()
    {
		$start_time = microtime(true);
		
        $apiKey = env('EXPEDIA_RAPID_API_KEY');
        $sharedSecret = env('EXPEDIA_RAPID_SHARED_SECRET');

        $client = new RapidClient($apiKey, $sharedSecret);
        $property['checkin'] = "2023-12-10";
        $property['checkout'] = "2023-12-31";
        $property['occupancy'] = ["2"];
		$propertyIds = [
			"12537922", 
			"10231646",
			"10215116",
			"10630123",
			"10948924",
		];

        $propertyContentCall = new PropertyPriceCall($client, $property);

        $dataPrice = $propertyContentCall->getPriceData($propertyIds);

        Cache::put('dataPriceAll', json_encode($dataPrice), 3600);

        $value = Cache::get('dataPriceAll');

        // dump('$value', json_decode($value));
        \Log::debug('RapidPropertyPriceCall', ['value' => json_decode($value)]);

		$end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        $this->info('Import completed. ' . round($execution_time, 2) . " seconds");
    }
}
