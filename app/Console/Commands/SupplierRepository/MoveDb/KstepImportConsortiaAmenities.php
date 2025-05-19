<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use App\Models\Configurations\ConfigConsortium;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ProductConsortiaAmenity;

class KstepImportConsortiaAmenities extends Command
{
    protected $signature = 'move-db:consortia-amenities';

    protected $description = 'Import Consortia Amenities from donor database';

    public function handle()
    {
        $this->warn('-> K step Import Consortia Amenities');

        $donorHotels = DB::connection('donor')->select('
            select id, virtuoso_amenities, signature_amenities, travel_leaders_amenities
            from hotels
        ');

        $this->newLine();

        $this->withProgressBar($donorHotels, function ($donorHotel) {
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($donorHotel) {
                $query->where('crm_hotel_id', $donorHotel->id);
            })->first();

            if (! $hotel || ! $hotel->product) {
                return;
            }

//            $this->addConsortiaAmenity($hotel->product->id, 'Virtuoso', $donorHotel->virtuoso_amenities);
//            $this->addConsortiaAmenity($hotel->product->id, 'Travel Leaders', $donorHotel->travel_leaders_amenities);
//            $this->addConsortiaAmenity($hotel->product->id, 'Signature', $donorHotel->signature_amenities);

            $affiliations = DB::connection('donor')->select('
                select hotel_id, value, amenities, created_at, updated_at
                from hotel_affiliations
                where hotel_id = :hotel_id
            ', ['hotel_id' => $donorHotel->id]);

            foreach ($affiliations as $affiliation) {
                $consortium = $affiliation->value === 'Travel Leaders Select' ? 'Travel Leaders' : $affiliation->value;
                $this->addConsortiaAmenity($hotel->product->id, $consortium, $affiliation->amenities, $affiliation->created_at);
            }
        });

        $this->info("\nConsortia Amenities imported successfully.");
    }

    private function addConsortiaAmenity($productId, $consortiumName, $description, $startDate = null)
    {
        if (! $description) {
            return;
        }
        $consortium = ConfigConsortium::where('name', $consortiumName)->first();
        if ($consortium) {
            ProductConsortiaAmenity::updateOrCreate(
                [
                    'product_id' => $productId,
                    'consortia_id' => $consortium->id,
                    'description' => $description,
                    'start_date' => $startDate,
                ]
            );
            $this->output->write("\033[1A\r\033[KAdded amenity for product ID {$productId} under consortium {$consortiumName}.\n");
        }
    }
}
