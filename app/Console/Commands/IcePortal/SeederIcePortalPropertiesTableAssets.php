<?php

namespace App\Console\Commands\IcePortal;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SeederIcePortalPropertiesTableAssets extends Command
{
    protected $signature = 'seeder-ice-portal-assets';

    protected $description = 'Command description';

    protected PendingRequest $client;
    // protected const TOKEN = 'bE38wDtILir6aJWeFHA2EnHZaQQcwdFjn7PKFz3A482bcae2';
    // protected const BASE_URI = 'https://ddwlx1ki3fks2.cloudfront.net';

    protected const TOKEN = 'hbm7hrirpLznIX9tpC0mQ0BjYD9PXYArGIDvwdPs5ed1d774';

    protected const BASE_URI = 'http://localhost:8008';

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(self::TOKEN)->timeout(120);
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->cities() as $city) {
            $startTime = microtime(true);
            $this->warn($city.' started');

            $codeCity = $this->getcityCode($city);
            $this->info($city.' codeCity '.$codeCity);

            $data = $this->makeSearch($codeCity);
            if ($data['success'] === 0 || ! isset($data['data']['results']) || is_null($data)) {
                $this->error($city.' error '.$codeCity);

                continue;
            }
            $runTime = microtime(true) - $startTime;
            $this->info($city.' completed '.$codeCity.' - '.$data['success'].
                ' count: '.count($data['data']['results']['general']).
                ' runTime: '.$runTime.' seconds');
        }
    }

    /**
     * @param int $codeCity
     * @return array
     */
    private function makeSearch(int $codeCity = 961): array
    {
        $requestData = [
            'type' => 'hotel',
            'destination' => $codeCity,
            'page' => 1,
            'results_per_page' => 500,
        ];

        $response = $this->client->post(self::BASE_URI.'/api/content/search', $requestData);

        return $response->json();
    }

    /**
     * @param string $city
     * @return int
     */
    private function getcityCode(string $city): int
    {
        $response = $this->client->get(self::BASE_URI.'/api/content/destinations', ['city' => $city]);
        $data = $response->json();

        return $data['data'][0]['city_id'] ?? 0;
    }

    /**
     * @return string[]
     */
    private function cities(): array
    {
        return [
            'Rome',
            'Milan',
            'Venice',
            'Florence',
            'Naples',
            'Pisa',
            'Paris',
            'Marseille',
            'Lyon',
            'Nice',
            'Barcelona',
            'Madrid',
            'Seville',
            'Valencia',
            'Granada',
            'New York',
            'Los Angeles',
            'Las Vegas',
            'San Francisco',
            'Miami',
            'Orlando',
            'Chicago',
            'New Orleans',
            'Washington',
            'Seattle',
            'Boston',
            'Austin',
            'Honolulu',
            'Denver',
            'Nashville',
            'San Diego',
            'Atlanta',
            'Philadelphia',
            'Portland',
            'Houston',
            'Beijing',
            'Shanghai',
            'Guangzhou',
            'Shenzhen',
            'Tokyo',
            'Kyoto',
            'Osaka',
            'Berlin',
            'Munich',
            'Hamburg',
            'Frankfurt',
            'Cologne',
            'Stuttgart',
            'Dusseldorf',
            'London',
            'Manchester',
            'Edinburgh',
            'Liverpool',
            'Birmingham',
            'Glasgow',
            'Bristol',
            'Oxford',
            'Cambridge',
            'Bangkok',
            'Phuket',
            'Chiang Mai',
            'Sydney',
            'Melbourne',
            'Brisbane',
            'Toronto',
            'Vancouver',
            'Montreal',
            'Calgary',
            'Ottawa',
            'Quebec City',
            'Edmonton',
            'Winnipeg',
            'Halifax',
            'Delhi',
            'Mumbai',
            'Jaipur',
            'Rio de Janeiro',
            'Sao Paulo',
            'Salvador',
        ];
    }
}
