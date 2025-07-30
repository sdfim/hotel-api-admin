<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

class MigrateMappings extends Command
{
    protected $signature = 'migrate:mappings';

    protected $description = 'Migrate existing mapping tables to the unified mappings table';

    public function handle()
    {
        $this->migrateExpedia();
        $this->migrateHBSI();
        $this->migrateIcePortal();

        $this->info('Mappings migrated successfully.');
    }

    private function migrateExpedia()
    {
        $mappings = DB::table('mapper_expedia_giatas')->get();

        foreach ($mappings as $mapping) {
            DB::table('mappings')->updateOrInsert(
                [
                    'giata_id' => $mapping->giata_id,
                    'supplier' => MappingSuppliersEnum::Expedia->value,
                    'supplier_id' => $mapping->expedia_id,
                    'match_percentage' => $mapping->step,
                ],
                [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        // Schema::dropIfExists('mapper_expedia_giatas');

    }

    private function migrateHBSI()
    {
        $mappings = DB::table('mapper_hbsi_giatas')->get();

        foreach ($mappings as $mapping) {
            DB::table('mappings')->updateOrInsert(
                [
                    'giata_id' => $mapping->giata_id,
                    'supplier' => MappingSuppliersEnum::HBSI->value,
                    'supplier_id' => $mapping->hbsi_id,
                    'match_percentage' => $mapping->perc,
                ],
                [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        // Schema::dropIfExists('mapper_hbsi_giatas');
    }

    private function migrateIcePortal()
    {
        $mappings = DB::table('mapper_ice_portal_giatas')->get();

        foreach ($mappings as $mapping) {
            DB::table('mappings')->updateOrInsert(
                [
                    'giata_id' => $mapping->giata_id,
                    'supplier' => MappingSuppliersEnum::IcePortal->value,
                    'supplier_id' => $mapping->ice_portal_id,
                    'match_percentage' => $mapping->perc,
                ],
                [
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        // Schema::dropIfExists('mapper_ice_portal_giatas');
    }
}
