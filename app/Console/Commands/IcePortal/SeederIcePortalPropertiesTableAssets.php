<?php

namespace App\Console\Commands\IcePortal;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SeederIcePortalPropertiesTableAssets extends Command
{
    protected $signature = 'seeder-ice-portal-assets {p}';

    protected $description = 'Command description';

    protected PendingRequest $client;

    protected const TOKEN = 'bE38wDtILir6aJWeFHA2EnHZaQQcwdFjn7PKFz3A482bcae2';

    protected const BASE_URI = 'https://ddwlx1ki3fks2.cloudfront.net';

    //    protected const TOKEN = 'hbm7hrirpLznIX9tpC0mQ0BjYD9PXYArGIDvwdPs5ed1d774';
    //    protected const BASE_URI = 'http://localhost:8008';

    public function __construct()
    {
        parent::__construct();
        $this->client = Http::withToken(self::TOKEN)->timeout(3600);
    }

    public function handle(): void
    {
        $p = $this->argument('p');
        $this->info('seeder-ice-portal-assets started '.$p);
        if ($p === 'all') {
            $ct = DB::table('ujv_api.giata_properties')
                ->distinct()
                ->pluck('city');
            $count = count($ct);
        } else {
            $ct = $this->cities($p);
            $count = count($ct);
        }
        $i = 0;
        foreach ($ct as $city) {
            $i++;
            // temporary
            if ($i < 1000) continue;

            $startTime = microtime(true);
            $this->warn($city.' started '.$i.' of '.$count.' cities');

            $codeCity = $this->getcityCode($city);
            $this->info($city.' codeCity '.$codeCity);

            if ($codeCity === 0) {
                $this->error($city.' error '.$codeCity);

                continue;
            }

            $data = $this->makeSearch($codeCity);
            if ($data['success'] === 0 || ! isset($data['data']['results']) || is_null($data)) {
                $this->error($city.' error '.$codeCity);

                continue;
            }
            $runTime = microtime(true) - $startTime;
            $this->info($city.' completed '.$codeCity.' - '.$data['success'].
                ' count: '.(isset($data['data']['results']['IcePortal']) ?
                    count($data['data']['results']['IcePortal']) :
                    count($data['data']['results']['general'])).
                ' runTime: '.$runTime.' seconds');
        }
    }

    private function makeSearch(int $codeCity = 961): array
    {
        $requestData = [
            'type' => 'hotel',
            'destination' => $codeCity,
            'page' => 1,
            'results_per_page' => 500,
        ];

        $response = $this->client->post(self::BASE_URI.'/api/content/search', $requestData);

        return $response->json() ?? [];
    }

    private function getcityCode(string $city): int
    {
        $response = $this->client->get(self::BASE_URI.'/api/content/destinations', ['city' => $city]);
        $data = $response->json();

        return $data['data'][0]['city_id'] ?? 0;
    }

    /**
     * @return string[]
     */
    private function cities(int $p): array
    {
        $cities[1] = [
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
        $cities[2] = [
            'Aurora',
            'Anaheim',
            'Santa Ana',
            'Corpus Christi',
            'Riverside',
            'St. Louis',
            'Lexington',
            'Stockton',
            'Pittsburgh',
            'Saint Paul',
            'Anchorage',
            'Cincinnati',
            'Henderson',
            'Greensboro',
            'Plano',
            'Newark',
            'Toledo',
            'Lincoln',
            'Orlando',
            'Chula Vista',
            'Jersey City',
            'Chandler',
            'Madison',
            'Lubbock',
            'Durham',
            'Fort Wayne',
            'St. Petersburg',
            'Laredo',
            'Buffalo',
            'Reno',
            'Gilbert',
            'Glendale',
            'Winston-Salem',
            'North Las Vegas',
            'Norfolk',
            'Chesapeake',
            'Garland',
            'Irving',
            'Hialeah',
            'Fremont',
            'Boise',
            'Richmond',
            'Baton Rouge',
            'Spokane',
            'Des Moines',
            'Montgomery',
            'Tacoma',
            'Shreveport',
            'San Bernardino',
            'Modesto',
        ];
        $cities[3] = [
            'Phoenix',
            'Dallas',
            'Houston',
            'San Antonio',
            'Austin',
            'Fort Worth',
            'San Jose',
            'Jacksonville',
            'Columbus',
            'Charlotte',
            'Indianapolis',
            'San Francisco',
            'Columbus',
            'Fort Worth',
            'El Paso',
            'Detroit',
            'Memphis',
            'Seattle',
            'Denver',
            'Washington',
            'Boston',
            'Nashville',
            'Baltimore',
            'Oklahoma City',
            'Louisville',
            'Portland',
            'Las Vegas',
            'Milwaukee',
            'Albuquerque',
            'Tucson',
            'Fresno',
            'Sacramento',
            'Long Beach',
            'Kansas City',
            'Mesa',
            'Virginia Beach',
            'Atlanta',
            'Colorado Springs',
            'Raleigh',
            'Omaha',
            'Miami',
            'Oakland',
            'Tulsa',
            'Minneapolis',
            'Wichita',
            'New Orleans',
            'Arlington',
            'Cleveland',
            'Tampa',
            'Bakersfield',
        ];
        $cities[4] = [
            'Amsterdam',
            'Vienna',
            'Zurich',
            'Stockholm',
            'Copenhagen',
            'Dublin',
            'Brussels',
            'Prague',
            'Warsaw',
            'Budapest',
            'Athens',
            'Istanbul',
            'Dubai',
            'Marrakech',
            'Cairo',
            'Cape Town',
            'Nairobi',
            'Mauritius',
            'Havana',
            'Mexico City',
            'Buenos Aires',
            'Santiago',
            'Lima',
            'Quito',
            'Bogota',
            'Caracas',
            'Panama City',
            'Guatemala City',
            'San Salvador',
            'San Jose',
            'Helsinki',
            'Oslo',
            'Reykjavik',
            'Bucharest',
            'Sofia',
            'Ljubljana',
            'Vilnius',
            'Riga',
            'Tallinn',
            'Hanoi',
            'Ho Chi Minh City',
            'Phnom Penh',
            'Kuala Lumpur',
            'Jakarta',
            'Manila',
            'Auckland',
            'Wellington',
            'Christchurch',
        ];

        return $cities[$p] ?? [];
    }
}
