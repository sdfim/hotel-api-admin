<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\KeyMapping;
use Modules\HotelContentRepository\Models\KeyMappingOwner;

class SstepImportExternalIdentifiers extends Command
{
    protected $signature = 'move-db:external-identifiers';

    protected $description = 'Import external identifiers from the donor database';

    public function handle()
    {
        $this->warn('-> S step Import External Identifiers');

        // Fetch data from the donor hotels table
        $donorIdentifiers = DB::connection('donor')
            ->table('external_identifiers')
            ->where('local_type', 'Hotel')
            ->get();

        $this->newLine();

        $this->withProgressBar($donorIdentifiers, function ($donorIdentifier) {
            // Find the corresponding Hotel using crmMapping
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($donorIdentifier) {
                $query->where('crm_hotel_id', $donorIdentifier->local_id);
            })->first();

            if (! $hotel) {
                $this->output->write("\033[1A\r\033[KHotel with CRM ID {$donorIdentifier->local_id} not found.\n");

                return;
            }

            $donorExternalType = $donorIdentifier->external_type;
            $mappingOwnerName = match ($donorExternalType) {
                'TravelTek' => 'IBS Hotel ID',
                'GIATA' => 'Luxuira ID',
                default => 'IBS Hotel ID',
            };

            $keyMappingOwner = KeyMappingOwner::where('name', $mappingOwnerName)->first();

            if (! $keyMappingOwner) {
                $keyMappingOwner = KeyMappingOwner::create(['name' => $mappingOwnerName]);
            }

            KeyMapping::updateOrCreate(
                [
                    'product_id' => $hotel->product->id,
                    'key_mapping_owner_id' => $keyMappingOwner->id,
                    'key_id' => ($mappingOwnerName === 'Luxuira ID')
                        ? 'LUX'.$donorIdentifier->external_id
                        : $donorIdentifier->external_id,
                ],
                [
                    'created_at' => $donorIdentifier->created_at,
                    'updated_at' => $donorIdentifier->updated_at,
                ]
            );

            $this->output->write("\033[1A\r\033[KHotel ID {$hotel->id} | Identifier ID {$donorIdentifier->id} imported.\n");
        });

        $this->info("\nHotel commissions imported successfully.");
    }
}
