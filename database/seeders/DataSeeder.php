<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contains;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contain = new Contains();
        $contain->name = 'Flight';
        $contain->description = 'test contain flight';
        $contain->save();

        $contain = new Contains();
        $contain->name = 'Hotel';
        $contain->description = 'test contain hotel';
        $contain->save();

        $contain = new Contains();
        $contain->name = 'Transfer';
        $contain->description = 'test contain transfer';
        $contain->save();
    }
}
