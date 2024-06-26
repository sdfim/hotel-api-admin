<?php

namespace App\Console\Commands;

use App\Models\GiataPlace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadGiataPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download-giata-places';

    protected $description = 'Fetch Giata Geography data from the API';

    protected const BATCH = 500;

    public function handle(): void
    {
        $filename = 'giata/giata_places.json';

        if (Storage::exists($filename)) {
            $placesData = json_decode(Storage::get($filename), true);
        } else {
            $response = Http::withBasicAuth(config('giata.poi.username'), config('giata.poi.password'))
                ->timeout(60)
                ->get(config('giata.poi.base_uri').'places');

            Storage::put($filename, $response->getBody()->getContents());
            $placesData = json_decode(Storage::get($filename), true);
        }

        DB::beginTransaction();

        try {
            $chunks = array_chunk($placesData, self::BATCH);
            $this->info('Fetched '.count($placesData).' Giata Geography data');

            foreach ($chunks as $chunk) {
                $dataToInsert = [];

                foreach ($chunk as $geoData) {
                    $dataToInsert[] = [
                        'key' => $geoData['key'],
                        'parent_key' => $geoData['parent_key'],
                        'name_primary' => $geoData['name_primary'],
                        'type' => $geoData['type'],
                        'state' => $geoData['state'],
                        'country_code' => $geoData['country_code'],
                        'airports' => isset($geoData['airports'])
                            ? json_encode($geoData['airports'])
                            : json_encode([]), // 'airports' => 'array
                        'name_others' => json_encode($geoData['name_others']),
                        'tticodes' => json_encode($geoData['tticodes']),
                    ];
                }

                GiataPlace::insert($dataToInsert);
            }

            DB::commit();

            $this->info('Giata Geography data fetched successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('Failed to fetch Giata Geography data: '.$e->getMessage());
        }
    }
}
