<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRate;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class IstepImportRates extends Command
{
    protected $signature = 'move-db:rates';

    protected $description = 'Import rates from hotel_rate_plans';

    public function handle()
    {
        $this->warn('-> I step Import Rates');

        $ratePlans = DB::connection('donor')->select('
            select distinct rp.hotel_id, rp.rate_code, rp.cancellation_policy, rp.description
            from hotel_rate_plans as rp
        ');

        $totalRatePlans = count($ratePlans);

        $this->newLine();

        $this->withProgressBar($ratePlans, function ($ratePlan) {
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($ratePlan) {
                $query->where('crm_hotel_id', $ratePlan->hotel_id);
            })->first();

            if ($hotel) {
                $hotelRate = HotelRate::firstOrCreate(
                    [
                        'hotel_id' => $hotel->id,
                        'code' => $ratePlan->rate_code,
                    ],
                    [
                        'name' => strip_tags($ratePlan->description),
                    ]
                );

                $rooms = $hotel->rooms->pluck('id')->toArray();
                $hotelRate->rooms()->sync($rooms);
//                $this->output->write("\033[1A\r\033[KRooms attached: ".implode(', ', $rooms)."\n");

                $cancellationPolicyType = ConfigDescriptiveType::where('name', 'Cancellation Policy')->first();

                ProductDescriptiveContentSection::firstOrCreate(
                    [
                        'product_id' => $hotelRate->hotel->product->id,
                        'rate_id' => $hotelRate->id,
                        'descriptive_type_id' => $cancellationPolicyType->id,
                    ],
                    [
                        'value' => $ratePlan->cancellation_policy,
                    ]
                );

                $this->output->write("\033[1A\r\033[KRate imported: {$hotel->id} | {$ratePlan->rate_code}\n");
            }
        });

        $this->info("\nImport rates from hotel_rate_plans completed successfully.");
    }
}
