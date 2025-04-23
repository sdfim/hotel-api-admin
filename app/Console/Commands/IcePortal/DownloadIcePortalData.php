<?php

namespace App\Console\Commands\IcePortal;

use App\Models\IcePortalPropertyAsset;
use App\Models\Mapping;
use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class DownloadIcePortalData extends Command
{
    protected $signature = 'download-iceportal-data';

    protected $description = 'Command description';

    protected PendingRequest $client;

    protected const TOKEN = 'sOTJHpaSjAtedYFpBItGPF2PwhnGv0GKXfPTEjkMd518cfbe';

    protected const BASE_URI = 'http://localhost:8008';

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(self::TOKEN)->timeout(3600);
    }

    public function handle(): void
    {
        $existingIds = IcePortalPropertyAsset::all()->pluck('listingID')->toArray();
        $giataIds = Mapping::IcePortal()
            ->whereNotIn('supplier_id', $existingIds)
            ->pluck('giata_id')->toArray();
        $chunks = array_chunk($giataIds, 10);
        $batchNumber = 1;

        foreach ($chunks as $chunk) {
            $st = microtime(true);
            $propertyIds = implode(',', $chunk);
            $response = $this->client->get(self::BASE_URI.'/api/v1/content/detail', [
                'type' => 'hotel',
                'property_ids' => $propertyIds,
            ]);

            $this->info('Batch '.$batchNumber.': '.implode(', ', $chunk));
            $this->info('Total Time: '.(microtime(true) - $st));
            $this->info('---------------------------------');
            $batchNumber++;
        }
    }
}
