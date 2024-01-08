<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use App\Models\ExpediaContent;
use App\Models\GiataProperty;
use App\Models\MapperExpediaGiata;

class MakeMapperExpediaGiateFuzzyWuzzy extends Command
{
    use BaseTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make-mapper-expedia-giate-fuzzy-wuzzy';

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
        $this->current_time['fuzzy-wuzzy'] = microtime(true);
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(): void
    {

        $this->executionTime('fuzzy-wuzzy');

        $mapper = [];

        $this->batch = 1;
        $arrExpedia = $this->fetchExpediaNeedMapping();

        $this->info('start fuzzy-wuzzy');

        foreach ($arrExpedia as $expedia) {

            try {
                $longitude = bcdiv($expedia['longitude'], 1, 2);
            } catch (Exception $e) {
                $longitude = -0.006;
            }

            $expediaStr = // $expedia['name'] . ' ' .
                $expedia['city'] . ' ' .
                bcdiv($expedia['latitude'], 1, 2) . ' ' .
                $longitude . ' ' .
                str_replace(['-'], '', $expedia['phone']) . ' ' .
                $expedia['address']['line_1'];
            $expediaCode = $expedia['property_id'];

            $this->warn('Expedia: ' . $expediaCode . ' - ' . $expediaStr . ' ' . $this->executionTime('fuzzy-wuzzy') . 's');

            $key = 'expedia_1_' . $expediaCode;

            if (Cache::has($key)) {
                $giata = Cache::get($key);
            } else {
                $giata = GiataProperty::select('*')
                    // ->whereRaw("MATCH(name) AGAINST('" . str_replace("'", ' ', $expedia['name']) . "' IN NATURAL LANGUAGE MODE)")
                    ->whereRaw("MATCH(mapper_address) AGAINST('" . str_replace("'", ' ', $expedia['address']['line_1']) . "' IN NATURAL LANGUAGE MODE)")
                    // ->where('phone', 'like', '%' . str_replace(['-'], '', $expedia['phone']) . '%')
                    ->where('latitude', 'like', bcdiv($expedia['latitude'], 1, 2) . '%')
                    ->where('longitude', 'like', $longitude . '%')
                    // ->where('city', 'like', '%' . $expedia['city'] . '%')
                    ->get()
                    ->toArray();

                Cache::put($key, $giata, 60 * 60 * 24);
            }

            if ($giata) {
                $this->info(" giata count " . count($giata) . ' | ' . $this->executionTime('fuzzy-wuzzy') . 's');

                foreach ($giata as $giataItem) {

                    try {
                        $longitudeGiata = bcdiv($expedia['longitude'], 1, 2);
                    } catch (Exception $e) {
                        $longitudeGiata = -0.006;
                    }
                    $giataStr = // $giataItem['name'] . ' ' .
                        $giataItem['city'] . ' ' .
                        bcdiv($giataItem['latitude'], 1, 2) . ' ' .
                        $longitudeGiata . ' ' .
                        $giataItem['mapper_phone_number'] . ' ' .
                        $giataItem['mapper_address'];

                    $giataCode = $giataItem['code'];

                    $sim1 = similar_text($expediaStr, $giataStr, $perc1);
                    $sim2 = similar_text($giataStr, $expediaStr, $perc2);

                    if ($perc1 > 77 || $perc2 > 77) {
                        $this->info('Expedia: ' . $expediaCode . ' - ' .
                            $expedia['name'] . ' | ' .
                            $giataCode . ' - ' .
                            $perc1 . ' | ' .
                            $perc2);

                        $maxPerc = 50;
                        if ($perc1 > $maxPerc) {
                            $maxPerc = $perc1;
                            $mapper[] = [
                                'expedia_id' => $expediaCode,
                                'giata_id' => $giataCode,
                                'step' => round($perc1, 0),
                            ];
                            $this->batch++;
                        }

                    }
                    // $this->error('Expedia: ' . $expediaCode . ' - ' .
                    // 		$expedia['name'] . ' | ' .
                    // 		$giataCode . ' - ' .
                    // 		$perc1 . ' | ' .
                    // 		$perc2);
                }
            }

            if ($this->batch % self::BATCH_SIZE == 0 && count($mapper) > 0) {
                MapperExpediaGiata::insertOrIgnore($mapper);
                $mapper = [];
                $this->warn('batch insertOrIgnore ' . $this->batch);
                $this->batch = 0;
            }
        }
    }

    /**
     * @return iterable
     */
    private function fetchExpediaNeedMapping(): iterable
    {
        $query = ExpediaContent::select('property_id', 'name', 'city', 'latitude', 'longitude', 'phone', 'address')
            ->leftJoin('mapper_expedia_giatas', 'expedia_content_main.property_id', '=', 'mapper_expedia_giatas.expedia_id')
            ->whereNull('mapper_expedia_giatas.giata_id')
            // ->leftJoin('report_mapper_expedia_giata', 'report_mapper_expedia_giata.expedia_id', '=', 'expedia_content_main.property_id')
            // ->whereNull('report_mapper_expedia_giata.expedia_id')
            ->where('expedia_content_main.rating', '>=', 2)
            ->where('expedia_content_main.rating', '<', 3)
            ->where('expedia_content_main.property_id', '>', 34993763)
            ->cursor();

        foreach ($query as $row) {
            yield $row;
        }
    }
}
