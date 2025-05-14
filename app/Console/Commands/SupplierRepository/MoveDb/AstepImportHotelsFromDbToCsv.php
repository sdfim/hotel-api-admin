<?php

namespace App\Console\Commands\SupplierRepository\MoveDb;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class AstepImportHotelsFromDbToCsv extends Command
{
    protected $signature = 'move-db:hotels-from-db-to-csv';

    protected $description = 'Import hotels from the database and interact with Elasticsearch';

    public function handle()
    {
        $this->warn('-> A step Import Hotels From db to csv');

        $esConfig = config('open-search.connections.elasticsearch');

        $es = ClientBuilder::create()
            ->setHosts([$esConfig['host']])
            ->setBasicAuthentication($esConfig['user'], $esConfig['pass'])
            ->build();

        $hotels = DB::connection('donor')->select('
            select distinct h.name as name, h.id as id, h.geo_latitude as geo_latitude, h.geo_longitude as geo_longitude, d.name as location
            from hotels as h
            left join destinations as d on h.destination_id = d.id
            where h.deleted_at is null and h.chain is not null
        ');

        $outputPath = 'hotels_output.csv';
        $writer = Writer::createFromPath(Storage::path($outputPath), 'w+');
        $fieldnames = ['id', 'name', 'locale', 'geo_latitude', 'geo_longitude', 'code', 'score'];
        $writer->insertOne($fieldnames);

        $mapperDonor = DB::connection('donor')->select("SELECT * FROM external_identifiers WHERE external_type = 'GIATA' AND local_id != 230");

        $mapperDonorArray = [];
        foreach ($mapperDonor as $map) {
            $mapperDonorArray[$map->local_id] = $map->external_id;
        }

        $this->newLine();

        $this->withProgressBar($hotels, function ($hotel) use ($es, $writer, $mapperDonorArray) {
            $hotel = (array) $hotel;
            $name = Arr::get($hotel, 'name');
            $locale = Arr::get($hotel, 'location');
            $lat = (float) Arr::get($hotel, 'geo_latitude', null);
            $lon = (float) Arr::get($hotel, 'geo_longitude', null);

            // Replace special characters in the name
            $name = str_replace(['/', '(', ')', '!'], ['\/', '\(', '\)', '\!'], $name);

            $this->output->write("\033[1A\r\033[KProcessing hotel: {$name} ({$lat}, {$lon})\n");

            if ($lat == 0 || $lon == 0) {
                $query = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['match' => ['name' => $name]],
                                ['match' => ['locale' => $locale]],
                            ],
                        ],
                    ],
                    'size' => 3,
                ];
            } else {
                $query = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['query_string' => ['query' => $name]],
                            ],
                            'filter' => [
                                ['range' => ['latitude' => ['gte' => $lat - 0.5, 'lte' => $lat + 0.5]]],
                                ['range' => ['longitude' => ['gte' => $lon - 0.5, 'lte' => $lon + 0.5]]],
                            ],
                        ],
                    ],
                    'size' => 3,
                ];
            }

            $response = $es->search(['index' => 'giata_properies_v2', 'body' => $query]);

            if ($response['hits']['total']['value'] > 0) {
                $first_hit = $response['hits']['hits'][0];
                if (isset($mapperDonorArray[$hotel['id']])) {
                    $code = $mapperDonorArray[$hotel['id']];
                    $score = 100;
                } else {
                    $code = $first_hit['_source']['code'] ?? '';
                    $score = $first_hit['_score'] ?? 0;
                }
            } else {
                $code = '';
                $score = 0;
            }

            $row = [
                'id' => $hotel['id'],
                'name' => $name,
                'locale' => $locale,
                'geo_latitude' => $lat,
                'geo_longitude' => $lon,
                'code' => $code,
                'score' => $score,
            ];

            $writer->insertOne($row);
        });

        $this->info("\nHotels Import hotels from the database and interact with Elasticsearch - successfully.");
    }
}
