<?php

namespace App\Console\Commands\Expedia;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;

class ExpediaInactive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expedia-inactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
	protected RapidClient $rapidClient;

	public function __construct()
	{
		parent::__construct();
		$this->rapidClient = new RapidClient();
	}

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $addHeaders = [
            'Customer-Ip' => '5.5.5.5',
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Test' => 'standard'
        ];
        $response = $this->rapidClient->get('v3/properties/inactive', ['since' => Carbon::now()->format('Y-m-d')], $addHeaders);
        $dataResponse = json_decode($response->getBody()->getContents(), true);

		if (is_array($dataResponse)) {
			$propertyIds = [];
			foreach ($dataResponse as $item) {
				if (isset($item['property_id'])) {
					$propertyIds[] = $item['property_id'];
				}
			}
		}

		$this->info('Import completed. ' . json_encode($propertyIds));
    }
}
