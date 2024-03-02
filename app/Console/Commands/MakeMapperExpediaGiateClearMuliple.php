<?php

namespace App\Console\Commands;

use App\Models\ExpediaContent;
use App\Models\GiataProperty;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeMapperExpediaGiateClearMuliple extends Command
{
    use BaseTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make-mapper-expedia-giate-clear-mapper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     *
     */
    private const BATCH_SIZE = 10;
    /**
     *
     */
    private const BATCH_SIZE_ADVANCED = 10;

    /**
     * @var int
     */
    private int $batch = 1;

    /**
     * @var int
     */
    private int $batchReport = 1;

    public function __construct()
    {
        parent::__construct();
        $this->current_time['clear-mapper'] = microtime(true);
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(): void
    {

        $this->executionTime('clear-mapper');

        $this->batch = 1;
        $mapper = $this->fetchMapperData();

        foreach ($mapper as $item) {
            $this->info('expedia_id: ' . $item->expedia_id);

            $expedia = ExpediaContent::where('property_id', $item->expedia_id)->with('mapperGiataExpedia')->first()->toArray();

            $this->info('step: ' . $expedia['mapper_giata_expedia'][0]['step']);

            $arr = [];
            if ($expedia['mapper_giata_expedia'][0]['step'] > 50) {
                foreach ($expedia['mapper_giata_expedia'] as $expediaItem) {
                    $arr[$expediaItem['giata_id']] = $expediaItem['step'];
                }
            } else {
                $giataIds = array_column($expedia['mapper_giata_expedia'], 'giata_id');
                $this->warn('giataIds: ' . json_encode($giataIds) . ' ' . $this->executionTime('clear-mapper') . 's');

                $name = str_replace([',', '-'], ' ', $expedia['name']);
                $listIgnore = ['The', 'Hotel'];
                foreach ($listIgnore as $ignore) {
                    $name = str_replace($ignore, '', $name);
                }
                $latitude = bcdiv($expedia['latitude'], 1, 2);
                $longitude = bcdiv($expedia['longitude'], 1, 2);
                $expediaStr = $name . ' ' .
                    $expedia['city'] . ' ' .
                    $latitude . ' ' .
                    $longitude . ' ' .
                    str_replace(['-'], '', $expedia['phone']) . ' ' .
                    $expedia['address']['line_1'];

                $giatas = GiataProperty::whereIn('code', $giataIds)->get();

                foreach ($giatas as $giata) {
                    $latitude = bcdiv($giata->latitude, 1, 2);
                    $longitude = $giata->longitude > 0.01 ? bcdiv($giata->longitude, 1, 2) : 0.01;
                    $giataStr = $giata->name . ' ' .
                        $giata->city . ' ' .
                        $latitude . ' ' .
                        $longitude . ' ' .
                        str_replace(['-'], '', $giata->mapper_phone_number) . ' ' .
                        $giata->mapper_address;

                    $sim1 = similar_text($expediaStr, $giataStr, $perc1);
                    $arr[$giata->code] = $perc1;
                    $this->warn('giata_id: ' . $giata->code . ' perc1: ' . $perc1);
                }
            }

            if (empty($arr)) continue;

            $this->warn('arr: ' . json_encode($arr) . ' ' . $this->executionTime('clear-mapper') . 's');

            $maxValueKey = array_search(max($arr), $arr);
            unset($arr[$maxValueKey]);
            $deleteKeys = array_keys($arr);

            if (!empty($deleteKeys)) {
                $this->warn('DELETE query : DELETE FROM ujv_api.mapper_expedia_giatas WHERE giata_id IN (' . implode(',', $deleteKeys) . ') AND expedia_id = ' . $item->expedia_id);
                DB::delete('DELETE FROM ujv_api.mapper_expedia_giatas WHERE giata_id IN (' . implode(',', $deleteKeys) . ') AND expedia_id = ' . $item->expedia_id);
                $this->warn('deleteKeys: ' . json_encode($deleteKeys) . ' ' . $this->executionTime('clear-mapper') . 's');
            }

            $this->info('-----------------------------------');

        }

        $this->info('start clear-mapper');


    }

    /**
     * @return iterable
     */
    private function fetchMapperData(): iterable
    {
        return DB::table('ujv_api.mapper_expedia_giatas')
            ->select('expedia_id')
            ->whereBetween('step', [11, 11])
            ->groupBy('expedia_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
    }
}
