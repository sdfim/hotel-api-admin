<?php

namespace App\Console\Commands\Assistants;

use App\Models\Channel;
use App\Models\GeneralConfiguration;
use App\Models\Mapping;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Modules\API\Controllers\ApiHandlers\HotelApiHandler;
use Modules\Enums\SupplierNameEnum;

class GetHBSIData extends Command
{
    protected $signature = 'hbsi:get-data {giataId} {--checkin=} {--checkout=}';

    protected $description = 'Get HBSI data by request';

    public function handle()
    {
        $giataId = $this->argument('giataId');
        $cacheKey = 'hbsi_supplier_data_'.$giataId;

        $cachedData = Cache::get($cacheKey);

        if ($cachedData !== null) {
            $this->info('Returned cached supplierDataForMerge with key: '.$cacheKey);
            $this->info(json_encode($cachedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return;
        }

        $mapperExists = Mapping::where('giata_id', $giataId)
            ->where('supplier', SupplierNameEnum::HBSI->value)
            ->exists();

        if (! $mapperExists) {
            $this->error('No mapper record found for supplier and giataId.');

            return;
        }

        $checkin = $this->option('checkin') ?? now()->addDays(60)->format('Y-m-d');
        $checkout = $this->option('checkout') ?? now()->addDays(61)->format('Y-m-d');
        $token = Channel::first()->access_token;
        $token = explode('|', $token)[1] ?? $token;

        $requestData = [
            'type' => 'hotel',
            'rating' => 2,
            'giata_ids' => [$giataId],
            'supplier' => SupplierNameEnum::HBSI->value,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'occupancy' => [['adults' => 2], ['adults' => 1]],
        ];

        $suppliersIds = GeneralConfiguration::pluck('currently_suppliers')->first() ?? [1];

        $request = new Request($requestData);
        $request->headers->set('Authorization', 'Bearer '.$token);

        /** @var HotelApiHandler $handler */
        $handler = app(HotelApiHandler::class);
        $response = $handler->price($request, $suppliersIds);

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            ob_start();
            $response->send();
            $content = ob_get_clean();
            $responseData = json_decode($content, true);
            $roomGroups = Arr::get($responseData, 'data.results.0.room_groups', []);
            $uniqueSupplierData = [];
            foreach ($roomGroups as $roomGroup) {
                if (isset($roomGroup['rooms'])) {
                    foreach ($roomGroup['rooms'] as $room) {
                        if (isset($room['supplier_room_name']) && isset($room['room_type'])) {
                            $key = $room['room_type'].'|'.$room['supplier_room_name'];
                            $uniqueSupplierData[$key] = [
                                'code' => $room['room_type'],
                                'name' => $room['supplier_room_name'],
                            ];
                        }
                    }
                }
            }
            $supplierDataForMerge = array_values($uniqueSupplierData);
            // Cache the supplier data for merge with giataId prefix
            Cache::put($cacheKey, $supplierDataForMerge, now()->addHour());
            $this->info('Cached supplierDataForMerge with key: '.$cacheKey);
            logger()->debug('LoggerFlowHotel _ GetHBSIData Command', ['cacheKey' => $cacheKey, 'output' => $supplierDataForMerge]);
        }

        $this->info('Status: '.$response->getStatusCode());
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $this->info('Data fetched successfully.');
            $this->info(json_encode($supplierDataForMerge, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        } else {
            $this->error('Failed to fetch data: '.$response->body());
        }
    }
}
