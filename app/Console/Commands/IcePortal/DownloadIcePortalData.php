<?php

namespace App\Console\Commands\IcePortal;

use App\Models\Mapping;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\API\Suppliers\IceSupplier\IceHBSIClient;

class DownloadIcePortalData extends Command
{
    protected $signature = 'download-iceportal-data';

    protected $description = 'Download IcePortal data';

    public function __construct(
        protected IceHBSIClient $client
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $mapperItems = Cache::remember('iceportal_mapper_items', 0.5, function () {
            return Mapping::IcePortal()
                ->where('supplier', 'IcePortal')
                ->get();
        });

        $mapperItems->chunk(10)->each(function ($chunk) use (&$batchNumber) {
            $st = microtime(true);

            $supplierIds = $chunk->pluck('supplier_id')->toArray();
            $supplierIds = implode(', ', $supplierIds);

            $this->client->processListings($chunk, []);

            $this->info('Batch '.$batchNumber.': '.$supplierIds);
            $this->info('Total Time: '.(microtime(true) - $st));
            $this->info('---------------------------------');
            $batchNumber++;
        });
    }
}
