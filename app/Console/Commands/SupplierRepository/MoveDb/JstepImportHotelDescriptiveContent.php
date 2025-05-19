<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use App\Models\Configurations\ConfigDescriptiveType;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Enums\HotelSaleTypeEnum;
use Modules\HotelContentRepository\Models\Hotel;
use Modules\HotelContentRepository\Models\HotelWebFinder;
use Modules\HotelContentRepository\Models\HotelWebFinderUnit;
use Modules\HotelContentRepository\Models\ProductDescriptiveContentSection;

class JstepImportHotelDescriptiveContent extends Command
{
    protected $signature = 'move-db:hotel-descriptive-content';

    protected $description = 'Import hotel descriptive content from the donor database';

    public function handle()
    {
        $this->warn('-> J step Import Hotel Descriptive Content');

        $donorHotels = DB::connection('donor')->select('
            select id, website, website_search_parameters, default_type, meta_description, cancel_policy, payment_policy, pet_policy, notes
            from hotels
        ');

        $this->newLine();

        $this->withProgressBar($donorHotels, callback: function ($donorHotel) {
            $hotel = Hotel::whereHas('crmMapping', function ($query) use ($donorHotel) {
                $query->where('crm_hotel_id', $donorHotel->id);
            })->first();

            if ($hotel && $hotel->product) {
                $type = $donorHotel->default_type;
                $defaultType = match ($type) {
                    'Commission Tracking' => HotelSaleTypeEnum::COMMISSION_TRACKING->value,
                    default => HotelSaleTypeEnum::DIRECT_CONNECTION->value,
                };
                $hotel->sale_type = $defaultType;
                $hotel->save();

                $hotel->product->website = $donorHotel->website;
                $hotel->product->save();

                if ($donorHotel->website_search_parameters) {
                    $websiteSearchParameters = json_decode($donorHotel->website_search_parameters, true);
                    $finder = Arr::get($websiteSearchParameters, 'search_url_endpoint', '').'?';
                    $finder .= Arr::get($websiteSearchParameters, 'search_adults_name')
                        ? Arr::get($websiteSearchParameters, 'search_adults_name').'={search_adults_name}&'
                        : '';
                    $finder .= Arr::get($websiteSearchParameters, 'search_nights_name')
                        ? Arr::get($websiteSearchParameters, 'search_nights_name').'={search_nights_name}&'
                        : '';
                    $finder .= Arr::get($websiteSearchParameters, 'search_children_name')
                        ? Arr::get($websiteSearchParameters, 'search_children_name').'={search_children_name}&'
                        : '';
                    $finder .= Arr::get($websiteSearchParameters, 'search_rooms_count_name')
                        ? Arr::get($websiteSearchParameters, 'search_rooms_count_name').'={search_rooms_count_name}&'
                        : '';
                    $finder .= Arr::get($websiteSearchParameters, 'search_end_travel_date_name')
                        ? Arr::get($websiteSearchParameters, 'search_end_travel_date_name')
                        .'={search_end_travel_date_name:'.(Arr::get($websiteSearchParameters, 'search_end_travel_date_format', 'Y-m-d') ?? 'Y-m-d').'}&'
                        : '';
                    $finder .= Arr::get($websiteSearchParameters, 'search_start_travel_date_name')
                        ? Arr::get($websiteSearchParameters, 'search_start_travel_date_name')
                        .'={search_start_travel_date_name:'.(Arr::get($websiteSearchParameters, 'search_start_travel_date_format', 'Y-m-d') ?? 'Y-m-d').'}&'
                        : '';
                    $finder .= Arr::get($websiteSearchParameters, 'search_property_identifier_name')
                        ? Arr::get($websiteSearchParameters, 'search_property_identifier_name')
                        .'='.Arr::get($websiteSearchParameters, 'search_property_identifier_value').'&'
                        : '';
                    $finder = rtrim($finder, '&');
                    $webFinder = HotelWebFinder::updateOrCreate(
                        ['base_url' => $websiteSearchParameters['search_url_endpoint']],
                        ['website' => 'pricing', 'finder' => $finder]
                    );
                    foreach ($websiteSearchParameters as $key => $searchParameter) {
                        if ($key === 'search_url_endpoint'
                            || $key === 'search_start_travel_date_format'
                            || $key === 'search_end_travel_date_format'
                            || $key === 'search_property_identifier_value'
                            || ! $searchParameter) {
                            continue;
                        }
                        HotelWebFinderUnit::updateOrCreate(
                            [
                                'web_finder_id' => $webFinder->id,
                                'field' => $key,
                            ],
                            [
                                'value' => $searchParameter,
                                'type' => match ($key) {
                                    'search_start_travel_date_name' => $websiteSearchParameters['search_start_travel_date_format'] ?? 'Y-m-d',
                                    'search_end_travel_date_name' => $websiteSearchParameters['search_end_travel_date_format'] ?? 'Y-m-d',
                                    'search_property_identifier_name' => $websiteSearchParameters['search_property_identifier_value'],
                                    default => null,
                                },
                            ]
                        );
                    }
                    $hotel->webFinders()->syncWithoutDetaching([$webFinder->id]);
                }

                $fields = [
                    'meta_description' => 'Meta Description',
                    'cancel_policy' => 'Cancellation Policy',
                    'payment_policy' => 'Payment Terms',
                    'pet_policy' => 'Pet Policy',
                    'notes' => 'Note',
                ];

                foreach ($fields as $field => $typeName) {
                    $type = ConfigDescriptiveType::where('name', $typeName)->first();
                    $donorHotelArray = (array) $donorHotel;

                    if ($type && isset($donorHotelArray[$field])) {
                        ProductDescriptiveContentSection::updateOrCreate(
                            [
                                'product_id' => $hotel->product->id,
                                'descriptive_type_id' => $type->id,
                            ],
                            [
                                'value' => $donorHotelArray[$field],
                            ]
                        );
                    }
                }

                $this->output->write("\033[1A\r\033[KHotel ID {$hotel->id} | {$donorHotel->id} descriptive content updated.\n");
            } else {
                $this->output->write("\033[1A\r\033[KHotel with CRM ID {$donorHotel->id} not found or has no product.\n");
            }
        });

        $this->info("\nImport hotel descriptive content completed successfully.");
    }
}
