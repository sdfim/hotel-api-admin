<?php

namespace App\Console\Commands;

use App\Models\GiataPoi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DownloadGiataPoi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download-giata-poi';

    protected $description = 'Fetch POIs from the API';

    protected const BATCH = 500;

    public function handle()
    {
        $response = Http::withBasicAuth(config('giata.poi.username'), config('giata.poi.password'))
            ->get(config('giata.poi.base_uri') . 'pois');

        $res = $response->getBody()->getContents();

        // TODO: it is a temporary solution for fixing the issue with the json format response
        $res = str_replace("\n", "", $res);
        $res = ltrim($res, "{");
        $res = rtrim($res, "}");
        $res = rtrim($res, ",");
        $res = '[{' . $res . ']';
//        Storage::put('Giata/giataPoi.json', $res);

        $pois = json_decode($res, true);

        DB::beginTransaction();

        try {
            $chunks = array_chunk($pois, self::BATCH);

            foreach ($chunks as $chunk) {
                $dataToInsert = [];

                foreach ($chunk as $poiData) {
                    $dataToInsert[] = [
                        'poi_id' => $poiData['id'],
                        'name_primary' => $poiData['name_primary'],
                        'type' => $poiData['type'],
                        'country_code' => $poiData['country_code'],
                        'lat' => $poiData['lat'],
                        'lon' => $poiData['lon'],
                        'places' => json_encode($poiData['places']),
                        'name_others' => json_encode($poiData['name_others']),
                    ];
                }

                GiataPoi::insert($dataToInsert);
            }

            DB::commit();

            $this->info('POIs fetched successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('Failed to fetch POIs: ' . $e->getMessage());
        }
    }
}
