<?php

namespace App\Console\Commands\Expedia;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;

class ExpediaGuestReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guest-reviews';

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
        $response = $this->rapidClient->get(
			'v3/properties/99802351/guest-reviews', [], $addHeaders);
        $dataResponse = json_decode($response->getBody()->getContents(), true);

		$this->info('Import completed. ' . json_encode($dataResponse));
    }
}
