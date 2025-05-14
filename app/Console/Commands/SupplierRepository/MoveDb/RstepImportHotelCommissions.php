<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Enums\CommissionValueTypeEnum;
use Modules\HotelContentRepository\Models\Commission;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\TravelAgencyCommission;

class RstepImportHotelCommissions extends Command
{
    protected $signature = 'move-db:hotel-commissions';

    protected $description = 'Import hotel commissions from the donor database';

    public function handle()
    {
        $this->warn('-> R step Import Hotel Commissions');

        // Find the commission with the name 'TA Commission'
        $taCommission = Commission::firstOrCreate(['name' => 'TA Commission']);
        $taxPercentage = Commission::firstOrCreate(['name' => 'Tax Percentage']);
        $netDiscount = Commission::firstOrCreate(['name' => 'UJV Margin']);

        if (! $taCommission) {
            $this->error("Commission with name 'TA Commission' not found.");

            return;
        }

        if (! $taxPercentage) {
            $this->error("Commission with name 'Tax Percentage' not found.");

            return;
        }

        if (! $netDiscount) {
            $this->error("Commission with name 'UJV Margin' not found.");

            return;
        }

        // Fetch data from the donor hotels table
        $donorHotels = DB::connection('donor')
            ->table('hotels')
            ->select('id', 'net_discount', 'ta_commission', 'tax_percentage')
            ->get();

        $this->newLine();

        $this->withProgressBar($donorHotels, function ($donorHotel) use ($taCommission, $taxPercentage, $netDiscount) {
            // Find the corresponding Hotel using crmMapping
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($donorHotel) {
                $query->where('crm_hotel_id', $donorHotel->id);
            })->first();

            if (! $hotel) {
                $this->output->write("\033[1A\r\033[KHotel with CRM ID {$donorHotel->id} not found.\n");
                return;
            }

            // Create a new TravelAgencyCommission record
            TravelAgencyCommission::updateOrCreate(
                [
                    'product_id' => $hotel->product->id,
                    'commission_id' => $netDiscount->id,
                    'date_range_start' => '2025-01-01',
                    'commission_value' => $donorHotel->net_discount,
                ],
                [
                    'commission_value_type' => CommissionValueTypeEnum::PERCENTAGE->value,
                ]
            );

            TravelAgencyCommission::updateOrCreate(
                [
                    'product_id' => $hotel->product->id,
                    'commission_id' => $taCommission->id,
                    'date_range_start' => '2025-01-01',
                    'commission_value' => $donorHotel->ta_commission,
                ],
                [
                    'commission_value_type' => CommissionValueTypeEnum::PERCENTAGE->value,
                ]
            );

            TravelAgencyCommission::updateOrCreate(
                [
                    'product_id' => $hotel->product->id,
                    'commission_id' => $taxPercentage->id,
                    'date_range_start' => '2025-01-01',
                    'commission_value' => $donorHotel->tax_percentage,
                ],
                [
                    'commission_value_type' => CommissionValueTypeEnum::PERCENTAGE->value,
                ]
            );

            $this->output->write("\033[1A\r\033[KCommission imported for hotel ID {$hotel->id}\n");
        });

        $this->info("\nHotel commissions imported successfully.");
    }
}
