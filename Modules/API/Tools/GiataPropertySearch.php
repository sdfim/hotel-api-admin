<?php

namespace Modules\API\Tools;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenSearch\ClientBuilder;

class GiataPropertySearch implements SearchInterface
{
    public function available(): bool
    {
        $connection = config('open-search.connection');

        try {
            if ($connection == 'aws') {
                $response = Http::withBasicAuth(config("open-search.connections.$connection.user"), config("open-search.connections.$connection.pass"))
                    ->get(config("open-search.connections.$connection.host"));
            } else {
                $response = Http::get(config("open-search.connections.$connection.host"));
            }
        } catch (Exception $e) {
            Log::error('GiataPropertySearch | available | open-search.connections ', [
                'message' => $e->getMessage(),
                '$connection' => $connection,
                'user' => config("open-search.connections.$connection.user"),
                'pass' => config("open-search.connections.$connection.pass"),
                'host' => config("open-search.connections.$connection.host"),
            ]);

            return false;
        }

        return $response->ok();
    }

    public function search(string $name, float $latitude, string $city): array
    {
        $connection = config('open-search.connection');
        $index = config("open-search.connections.$connection.index");

        if ($connection == 'elasticsearch') {
            $client = ClientBuilder::create()
                ->setHosts([config("open-search.connections.$connection.host")])
                ->build();
        } else {
            $config = config("open-search.connections.$connection");
            $client = ClientBuilder::create()
                ->setHosts([$config['host']])
                ->setSigV4Region($config['region'])
                ->setSigV4Service($config['service'])
                ->setSigV4CredentialProvider(true)
                ->setSigV4CredentialProvider([
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ])->build();
        }

        if ($latitude != 0) {
            $params = [
                'index' => $index,
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'match' => [
                                        'name' => $name,
                                    ],
                                ],
                                [
                                    'range' => [
                                        'latitude' => [
                                            'gte' => $latitude - 0.1,  // Adjust the range as needed
                                            'lte' => $latitude + 0.1,  // Adjust the range as needed
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            $params = [
                'index' => $index,
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'match' => [
                                        'name' => $name,
                                    ],
                                ],
                                [
                                    'match' => [
                                        'city' => $city,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        $response = $client->search($params);

        return array_map(fn($result): array => $result['_source'], $response['hits']['hits']);
    }
}
