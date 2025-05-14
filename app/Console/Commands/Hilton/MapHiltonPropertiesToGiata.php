<?php

namespace App\Console\Commands\Hilton;

use App\Models\HiltonProperty;
use App\Models\Mapping;
use App\Models\Supplier;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;
use League\Csv\Writer;

class MapHiltonPropertiesToGiata extends Command
{
    protected $signature = 'map:hilton-properties-to-giata';

    protected $description = 'Map Hilton properties to GIATA codes and export to CSV';

    public function handle()
    {
        $this->warn('-> Mapping Hilton Properties to GIATA');

        $esConfig = config('open-search.connections.elasticsearch');

        $es = ClientBuilder::create()
            ->setHosts([$esConfig['host']])
            ->setBasicAuthentication($esConfig['user'], $esConfig['pass'])
            ->build();

        $hiltonProperties = HiltonProperty::select(['prop_code', 'name', 'latitude', 'longitude'])->get();

        $outputPath = base_path('app/Console/Commands/Hilton/mapper_hilton.csv');
        $writer = Writer::createFromPath($outputPath, 'w+');
        $fieldnames = ['giata', 'giata_name', 'giata_latitude', 'giata_longitude', 'hilton', 'hilton_name', 'hilton_latitude', 'hilton_longitude', 'score'];
        $writer->insertOne($fieldnames);

        $supplierId = Supplier::getSupplierId('Hilton');

        if (! $supplierId) {
            $this->error('Supplier "Hilton" not found.');

            return;
        }

        $this->newLine();

        $this->withProgressBar($hiltonProperties, function ($property) use ($es, $writer) {
            $query = [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match' => ['name' => $property->name]],
                        ],
                        'filter' => [
                            ['range' => ['latitude' => ['gte' => $property->latitude - 0.5, 'lte' => $property->latitude + 0.5]]],
                            ['range' => ['longitude' => ['gte' => $property->longitude - 0.5, 'lte' => $property->longitude + 0.5]]],
                        ],
                    ],
                ],
                'size' => 1,
            ];

            $response = $es->search(['index' => 'giata_properies_v2', 'body' => $query]);

            $giata = '';
            $score = 0;
            $giataName = '';
            $giataLatitude = '';
            $giataLongitude = '';
            if ($response['hits']['total']['value'] > 0) {
                $first_hit = $response['hits']['hits'][0];
                $giata = $first_hit['_source']['code'] ?? '';
                $giataName = $first_hit['_source']['name'] ?? '';
                $giataLatitude = $first_hit['_source']['latitude'] ?? '';
                $giataLongitude = $first_hit['_source']['longitude'] ?? '';
                $score = $first_hit['_score'] ?? 0;
            }

            $row = [
                'giata' => $giata,
                'giata_name' => $giataName,
                'giata_latitude' => $giataLatitude,
                'giata_longitude' => $giataLongitude,

                'hilton' => $property->prop_code,
                'hilton_name' => $property->name,
                'hilton_latitude' => $property->latitude,
                'hilton_longitude' => $property->longitude,

                'score' => $score ?? 0,
            ];

            $writer->insertOne($row);

            // Save to Mapping model
            if ($giata) {
                $existingMapping = Mapping::where('giata_id', $giata)
                    ->where('supplier', 'Hilton')
                    ->first();

                if (! $existingMapping || $score > $existingMapping->match_percentage) {
                    Mapping::updateOrCreate(
                        [
                            'giata_id' => $giata,
                            'supplier' => 'Hilton',
                        ],
                        [
                            'supplier_id' => $property->prop_code,
                            'match_percentage' => $score,
                        ]
                    );
                }
            }
        });

        $this->info("\nMapping Hilton Properties to GIATA - successfully completed.");
    }
}
