<?php

namespace App\Console\Commands\Expedia;

use Illuminate\Console\Command;
use Modules\API\Suppliers\ExpediaSupplier\PropertyCallFactory;

class RapidPropertyContentCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:RapidPropertyContentCall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RapidPropertyContentCall';

    private PropertyCallFactory $rapidCallFactory;

    public function __construct(PropertyCallFactory $rapidCallFactory)
    {
        parent::__construct();
        $this->rapidCallFactory = $rapidCallFactory;
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(): void
    {
        $property['language'] = "en-US";
        $property['supplySource'] = "expedia";
        $property['countryCodes'] = "PL";
        $property['categoryIdExcludes'] = null;
        $property['propertyRatingMmin'] = 4;
        $property['propertyRatingMmax'] = 5;

        $propertyContentCall = $this->rapidCallFactory->createPropertyContentCall($property);

        $stream = $propertyContentCall->stream();
        $size = $propertyContentCall->size();

        \Log::debug('RapidPropertyContentCall', [
            'stream' => $stream,
            'size' => $size
        ]);
    }
}
