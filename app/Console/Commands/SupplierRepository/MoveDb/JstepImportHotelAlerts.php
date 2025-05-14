<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;
use Carbon\Carbon;

class JstepImportHotelAlerts extends Command
{
    protected $signature = 'move-db:hotel-alerts';

    protected $description = 'Import hotel alerts from the donor database';

    public function handle()
    {
        $this->warn('-> J step Import from Hotel Alerts');

        $donorAlerts = DB::connection('donor')->select('
            SELECT id, hotel_id, type, checkin, checkout, description, ujv_exclusive
            FROM hotel_alerts
        ');

        $this->newLine();

        $this->withProgressBar($donorAlerts, function ($donorAlert) {
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($donorAlert) {
                $query->where('crm_hotel_id', $donorAlert->hotel_id);
            })->first();

            if ($hotel && $hotel->product) {

                $type = ConfigDescriptiveType::firstOrCreate(
                    ['name' => 'Hotel Alert'],
                    ['description' => 'Hotel Alert', 'location' => 'all', 'type' => 'Alert']
                );

                ProductDescriptiveContentSection::updateOrCreate(
                    [
                        'product_id' => $hotel->product->id,
                        'descriptive_type_id' => $type->id,
                        'value' => $donorAlert->description,
                    ],
                    [
                        'start_date' => Carbon::parse($donorAlert->checkin),
                        'end_date' => Carbon::parse($donorAlert->checkout),
                    ]
                );

                $this->output->write("\033[1A\r\033[KHotel ID {$hotel->id} | Alert ID {$donorAlert->id} imported.\n");
            } else {
                $this->output->write("\033[1A\r\033[KHotel with CRM ID {$donorAlert->hotel_id} not found or has no product.\n");
            }
        });

        $this->info("\nImport hotel alerts completed successfully.");
    }
}
