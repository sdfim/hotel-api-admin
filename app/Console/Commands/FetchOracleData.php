<?php

namespace App\Console\Commands;

use App\Models\Mapping;
use App\Models\OracleContent;
use Illuminate\Console\Command;
use Modules\API\Suppliers\Oracle\Client\OracleClient;

class FetchOracleData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oracle:fetch-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch data from Oracle endpoints and store in the database';

    protected OracleClient $oracleClient;

    public function __construct(OracleClient $oracleClient)
    {
        $this->oracleClient = $oracleClient;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fetching data from Oracle endpoints...');

        $hotelIds = Mapping::oracle()->pluck('supplier_id')->unique()->toArray();

        foreach ($hotelIds as $hotelId) {
            $this->info("Processing hotel ID: $hotelId");

            try {
                $roomClasses = $this->oracleClient->getRoomClasses($hotelId);
                $rooms = $this->oracleClient->getRooms($hotelId);
                $roomTypes = $this->oracleClient->getRoomTypes($hotelId);

                OracleContent::updateOrCreate(
                    ['code' => $hotelId],
                    [
                        'room_classes' => $roomClasses,
                        'rooms' => $rooms,
                        'room_types' => $roomTypes,
                    ]
                );

                $this->info('Data successfully fetched and stored.');
            } catch (\Exception $e) {
                $this->error('Failed to fetch data: '.$e->getMessage());
            }
        }

        return 0;
    }
}
