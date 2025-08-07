<?php

namespace App\Console\Commands\HotelTrader;

use App\Models\Mapping;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;
use League\Csv\Reader;
use Modules\API\Suppliers\Enums\MappingSuppliersEnum;

// use OpenSearch\ClientBuilder;

class MakeMapperHotelTraderMappingsFromCsv extends Command
{
    protected $signature = 'make:mapper-hotel-trader-mappings-from-csv {csvPath?}';

    protected $description = 'Import HotelTrader mappings from CSV, match with Elasticsearch, and update mappings table.';

    public function handle(): void
    {
        $csvPath = $this->argument('csvPath') ?? null;
        if (! $csvPath) {
            $csvPath = __DIR__.'/HTR_property_static_data.csv';
        }
        if (! file_exists($csvPath)) {
            $this->error("CSV file not found: $csvPath");

            return;
        }

        $esConfig = config('open-search.connections.elasticsearch');
        $es = ClientBuilder::create()
            ->setHosts([$esConfig['host']])
            ->setBasicAuthentication($esConfig['user'], $esConfig['pass'])
            ->build();

        $reader = Reader::createFromPath($csvPath, 'r');
        $reader->setHeaderOffset(0);
        $records = iterator_to_array($reader->getRecords());

        $this->info('Processing HotelTrader CSV records...');
        $bar = $this->output->createProgressBar(count($records));
        $bar->start();

        $bestMatches = [];

        foreach ($records as $row) {
            $propertyId = $row['propertyId'] ?? null;
            $name = $row['propertyName'] ?? null;
            $locale = $row['state'] ?? null;
            $lat = isset($row['latitude']) ? (float) $row['latitude'] : null;
            $lon = isset($row['longitude']) ? (float) $row['longitude'] : null;

            if (! $propertyId || ! $name) {
                $bar->advance();

                continue;
            }

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
                // Properly quote and escape the name for query_string
                $quotedName = '"' . addcslashes($name, '"') . '"';
                $query = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['query_string' => ['query' => $quotedName]],
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
            if (($response['hits']['total']['value'] ?? 0) > 0) {
                foreach ($response['hits']['hits'] as $hit) {
                    $giataId = $hit['_source']['code'] ?? null;
                    $score = $hit['_score'] ?? 0;
                    if ($giataId) {
                        if (! isset($bestMatches[$giataId]) || $score > $bestMatches[$giataId]['score']) {
                            $bestMatches[$giataId] = [
                                'supplier_id' => $propertyId,
                                'score' => $score,
                            ];
                        }
                    }
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $this->info('Writing best matches to mappings table...');
        foreach ($bestMatches as $giataId => $data) {
            $existing = Mapping::where('giata_id', $giataId)
                ->where('supplier', MappingSuppliersEnum::HOTEL_TRADER->value)
                ->first();
            if ($existing) {
                if ($data['score'] > $existing->match_percentage) {
                    $existing->supplier_id = $data['supplier_id'];
                    $existing->match_percentage = $data['score'];
                    $existing->save();
                }
                // else: skip, as existing score is higher or equal
            } else {
                Mapping::create([
                    'giata_id' => $giataId,
                    'supplier' => MappingSuppliersEnum::HOTEL_TRADER->value,
                    'supplier_id' => $data['supplier_id'],
                    'match_percentage' => $data['score'],
                ]);
            }
        }
        $this->info('Done.');
    }
}
