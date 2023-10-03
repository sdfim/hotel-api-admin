<?php

namespace App\Console\Commands\Expedia;

use Illuminate\Console\Command;
use Modules\API\Suppliers\ExpediaSupplier\RapidClient;
use Modules\API\Suppliers\ExpediaSupplier\PropertyContentCall;
use Illuminate\Support\Facades\Cache;

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

    /**
     * Execute the console command.
     */
    public function handle ()
    {
        $apiKey = env('EXPEDIA_RAPID_API_KEY');
        $sharedSecret = env('EXPEDIA_RAPID_SHARED_SECRET');

        $client = new RapidClient($apiKey, $sharedSecret);
        $property['language'] = "en-US";
        $property['supplySource'] = "expedia";
        $property['countryCodes'] = "PL";
        $property['categoryIdExcludes'] = null;
        $property['propertyRatingMmin'] = 4;
        $property['propertyRatingMmax'] = 5;

        // dd($client, $language, $supplySource, $countryCodes, $categoryIdExcludes);

        $propertyContentCall = new PropertyContentCall($client, $property);

        $stream = $propertyContentCall->stream();
        $size = $propertyContentCall->size();

        // dump('$stream', $stream);
        echo 'size = ' . json_encode($size);

        Cache::put('stream', json_encode($stream), 3600);

        $value = Cache::get('stream');

        // dump('$value', json_decode($value));
        \Log::debug('RapidPropertyContentCall', ['value' => json_decode($value)]);
    }
}
