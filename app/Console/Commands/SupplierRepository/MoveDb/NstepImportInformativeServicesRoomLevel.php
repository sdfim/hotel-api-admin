<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use App\Models\Configurations\ConfigServiceType;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Enums\ProductApplyTypeEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelRoom;
use Modules\HotelContentRepository\Models\ProductInformativeService;
use Modules\HotelContentRepository\Models\ProductInformativeServiceDynamicColumn;

class NstepImportInformativeServicesRoomLevel extends Command
{
    protected $signature = 'move-db:informative-services-room-level';

    protected $description = 'Import Informative Services from donor database table hotel_default_extra_room_fees';

    public function handle()
    {
        $this->warn('-> N step Import Informative Services Room Level');

        $donorServices = DB::connection('donor')->table('hotel_default_extra_room_fees')
            ->select('hotel_default_extra_room_fees.*')
            ->get();

        $this->newLine();

        $this->withProgressBar($donorServices, function ($donorService) {
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($donorService) {
                $query->where('crm_hotel_id', $donorService->hotel_id);
            })->first();

            if (! $hotel || ! $hotel->product) {
                return;
            }

            $serviceName = 'Extra Room Fee';

            $configServiceType = ConfigServiceType::firstOrCreate(
                ['name' => $serviceName],
                ['description' => $serviceName, 'cost' => 0]
            );
            $applyType = match ($donorService->type) {
                'Person' => ProductApplyTypeEnum::PER_PERSON->value,
                'Person-Night' => ProductApplyTypeEnum::PER_NIGHT_PER_PERSON,
                default => ProductApplyTypeEnum::PER_NIGHT,
            };

            ProductInformativeService::updateOrCreate(
                [
                    'product_id' => $hotel->product->id,
                    'name' => $donorService->description,
                ],
                [
                    'service_id' => $configServiceType ? $configServiceType->id : null,
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                    'cost' => $donorService->rack,
                    'total_net' => $donorService->net,
                    'apply_type' => $applyType,
                    'currency' => 'USD',
                    'auto_book' => $donorService->automatic,
                    'show_service_on_pdf' => false,
                    'show_service_data_on_pdf' => false,
                    'commissionable' => false,
                ]
            );


            $this->output->write("\033[1A\r\033[KInformative service added for hotel ID {$hotel->id}.\n");
        });

        $this->info("\nInformative services imported successfully.");
    }
}
