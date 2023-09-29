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
    protected $signature = 'make-mapper-expedia-giate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private const BATCH_SIZE = 100;

    /**
     * Execute the console command.
     */
    public function handle ()
    {
        $batch = 1;
        $mapper = [];
        $arrExpedia = ExpediaContent::select('property_id', 'name')->get()->toArray();
        foreach ($arrExpedia as $expedia) {
            $giata = GiataProperty::where('name', $expedia['name'])->get()->toArray();
            if ($giata) {
                foreach ($giata as $giataItem) {
                    $this->info('Expedia: ' . $expedia['property_id'] . ' - ' . $expedia['name'] . ' - ' . $giataItem['code'] . ' - ' . $giataItem['name']);
                    $batch++;
                    $mapper[] = [
                        'expedia_id' => $expedia['property_id'],
                        'giata_id' => $giataItem['code'],
                    ];
                }
            }
            if ($batch % self::BATCH_SIZE == 0) {
                MapperExpediaGiata::insert($mapper);
                $mapper = [];
            }
        }
    }
}
