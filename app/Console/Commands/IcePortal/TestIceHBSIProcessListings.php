<?php

namespace App\Console\Commands\IcePortal;

use App\Models\Mapping;
use Illuminate\Console\Command;
use Modules\API\Suppliers\IceSupplier\IceHBSIClient;
use Modules\API\Suppliers\Transformers\IcePortal\IcePortalAssetTransformer;

class TestIceHBSIProcessListings extends Command
{
    protected $signature = 'test:icehbsi-process-listings {listingId}';

    protected $description = 'Test IceHBSIClient processListings with a single hotel';

    public function handle()
    {
        $listingId = $this->argument('listingId');

        $mapping = Mapping::IcePortal()
            ->where('supplier', 'IcePortal')
            ->where('supplier_id', $listingId)
            ->get();

        $client = new IceHBSIClient(new IcePortalAssetTransformer);
        $results = $client->processListings($mapping, []);

        $this->info('Results:');
        $this->line(print_r($results, true));
    }
}
