<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\Vendor;

class DstepImportVendors extends Command
{
    protected $signature = 'move-db:vendors';

    protected $description = 'Import vendors and update hotels';

    public function handle()
    {
        $this->warn('-> D step Import Vendors');

        $donorHotels = DB::connection('donor')->select('
            select id, chain
            from hotels
        ');

        $this->newLine();

        $this->withProgressBar($donorHotels, function ($record) {
            $hotelId = $record->id;
            $vendorName = $record->chain;

            if ($vendorName === null) {
                return;
            }

            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($hotelId) {
                $query->where('crm_hotel_id', $hotelId);
            })->first();

            if (! $hotel) {
                $this->output->write("\033[1A\r\033[KHotel not found: ID {$hotelId}\n");
                return;
            }

            if ($vendorName === 'Independent') {
                $vendor = Vendor::updateOrCreate(
                    ['name' => $hotel->product->name],
                    [
                        'address' => $hotel->address,
                        'lat' => $hotel->product->lat,
                        'lng' => $hotel->product->lng,
                        'verified' => 1,
                        'type' => ['hotel'],
                        'independent_flag' => 1,
                    ]
                );
            } else {
                $vendor = Vendor::updateOrCreate(
                    ['name' => $vendorName],
                    [
                        'address' => $hotel->address,
                        'lat' => $hotel->product->lat,
                        'lng' => $hotel->product->lng,
                        'verified' => 1,
                        'type' => ['hotel'],
                    ]
                );
            }

            $hotel->product->vendor_id = $vendor->id;
            $hotel->product->onSale = 1;
            $hotel->product->save();

//            Vendor::where('name', 'TEST')->delete();

            $this->output->write("\033[1A\r\033[KUpdated hotel ID {$hotelId} with vendor {$vendor->name}\n");
        });

        $this->info("\nVendor import and hotel update completed successfully.");

        return 0;
    }
}
