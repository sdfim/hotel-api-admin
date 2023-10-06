<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExpediaContent;
use App\Models\GiataProperty;
use App\Models\MapperExpediaGiata;

class MakeMapperExpediaGiate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make-mapper-expedia-giate {startId} {stepStrategy}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private const BATCH_SIZE = 100;
    private const BATCH_SIZE_ADVANCED = 10;
    private $batch = 1;
    private $startId = 1;
    private $stepStrategy = 4;

    /**
     * Execute the console command.
     */
    public function handle ()
    {
        $this->startId = $this->argument('startId'); // 1
        $this->stepStrategy = $this->argument('stepStrategy');


        $mapper = [];

        # step 1: where name = name, latitude like latitude(2 point after dot), longitude like longitude(2 point after dot)
        $this->batch = 1;
        $arrExpedia = $this->fetchExpediaNeedMapping();

        foreach ($arrExpedia as $expedia) {

            $latitude = substr($expedia['latitude'], 0, 5);
            $longitude = substr($expedia['longitude'], 0, 5);

            $giata = GiataProperty::where('name', $expedia['name'])
                ->where('position', 'like', '%' . $latitude . '%')
                ->where('position', 'like', '%' . $longitude . '%')
                ->get()
                ->toArray();
            if ($giata) {
                foreach ($giata as $giataItem) {
                    $this->info('Expedia: ' . $expedia['property_id'] . ' - ' . $expedia['name'] . ' - ' . $giataItem['code'] . ' - ' . $giataItem['name']);
                    $this->batch++;
                    $mapper[] = [
                        'expedia_id' => $expedia['property_id'],
                        'giata_id' => $giataItem['code'],
                        'step' => 1,
                    ];
                }
            }
            if ($this->batch % self::BATCH_SIZE == 0) {
                MapperExpediaGiata::insertOrIgnore($mapper);
                $mapper = [];
            }
        }

        # step 2 and more: where name like name, latitude like latitude(2 point after dot), longitude like longitude(2 point after dot)
        $arrExpedia = $this->fetchExpediaNeedMapping();

        foreach ($arrExpedia as $expedia) {

            $nameHotel = $expedia['name'];
            $nameArr = explode(' ', $expedia['name']);
            // if (count($nameArr) == 1) continue;
            $lastItem = array_pop($nameArr);
            $expediaNameArr = [];
            foreach ($nameArr as $v) {
                if ($v == $lastItem) continue;
                $expediaNameArr[] = $v;
            }
            $expediaNameStart = implode(' ', $expediaNameArr);

            $latitude = substr($expedia['latitude'], 0, 5);
            $longitude = substr($expedia['longitude'], 0, 5);
            $phone = str_replace('-', '', $expedia['phone']);
            $postCode = str_replace('-', '', $expedia['postal_code']);
            $state = $expedia['state_province_name'];
            $city = $expedia['city'];

            $strategy = [
                '2' => [
                    'position' => '%' . $latitude . '%',
                    'position ' => '%' . $longitude . '%',
                    'name' => $nameHotel . '%',
                ],
                '3' => [
                    'position' => '%' . $latitude . '%',
                    'position ' => '%' . $longitude . '%',
                    'name' => $expediaNameStart . '%',
                    'phone' => '%' . $phone . '%',
                ],
                '4' => [
                    'position' => '%' . $latitude . '%',
                    'position ' => '%' . $longitude . '%',
                    'phone' => '%' . $phone . '%',
                    'address' => '%' . $postCode . '%',
                ],
                // '5' => [
                // 	'position' => '%' . $latitude . '%',
                // 	'position ' => '%' . $longitude . '%',
                // 	'address' => '%' . $postCode . '%',
                // 	'city' => '%' . $city . '%',
                // 	'arrName' => $nameArr,
                // ],
                // '6' => [
                // 	'position' => '%' . $latitude . '%',
                // 	'position ' => '%' . $longitude . '%',
                // 	'address' => '%' . $postCode . '%',
                // 	'locale' => '%' . $state . '%',
                // 	'arrName' => $nameArr,
                //
                // ],
            ];

            $start = microtime(true);
            $mp = false;
            foreach ($strategy as $step => $params) {
                if ($step > $this->stepStrategy) continue;
                $giata = $this->query($params);
                if ($giata) {
                    $mapper = $this->addToMapper($mapper, $giata, $expedia, $step);
                    $mp = true;
                    $executionTime = (microtime(true) - $start);
                    $this->info("Expedia batch = " . $this->batch . ", executionTime = $executionTime");
                    break;

                }
            }
            if (!$mp) $this->error('Expedia: ' . $expedia['id'] . ' - ' . $expedia['property_id'] . ' - ' . $expedia['name']);

            if ($this->batch > self::BATCH_SIZE_ADVANCED) {
                MapperExpediaGiata::insert($mapper);
                $mapper = [];
                $this->batch = 1;
                $this->info("Expedia insertOrIgnore");
            }
        }
    }

    private function query ($params): array
    {
        $serch = GiataProperty::query();
        foreach ($params as $k => $param) {

            if ($k == 'arrName') {
                $serch->where(function ($query) use ($param) {
                    foreach ($param as $v) {
                        if ($v == 'Hotel') continue;
                        $query->orWhere('name', 'like', '%' . $v . '%');
                    }
                });
            } else $serch->where(trim($k), 'like', $param);
        }
        return $serch->get()->toArray();
    }

    private function addToMapper ($mapper, $giata, $expedia, $step): array
    {
        foreach ($giata as $giataItem) {
            $this->info("Expedia step-$step: " . $expedia['id'] . ' | ' . $expedia['property_id'] . ' | ' . $expedia['name'] .
                ' - ' . $giataItem['code'] . ' | ' . $giataItem['name']);
            $this->batch++;
            $mapper[] = [
                'expedia_id' => $expedia['property_id'],
                'giata_id' => $giataItem['code'],
                'step' => $step,
            ];
        }
        return $mapper;
    }

    private function fetchExpediaNeedMapping (): array
    {
        $existingMapper = MapperExpediaGiata::select('expedia_id')->get()->toArray();
        return ExpediaContent::select('id', 'property_id', 'name', 'latitude', 'longitude', 'phone', 'city', 'state_province_name', 'postal_code')
            ->where('id', '>=', $this->startId)
            ->whereNotIn('property_id', $existingMapper)
            ->get()
            ->toArray();
    }
}
