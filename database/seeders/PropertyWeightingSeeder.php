<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PropertyWeighting;
use App\Models\GiataProperty;


class PropertyWeightingSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $giataIds = GiataProperty::where('city', 'New York')->get()->pluck('code')->toArray();	

		$data = [];
		foreach ($giataIds as $key => $giataId) {
			$weight['property'] = $giataId;
			$weight['weight'] = rand(1, 10000);
			if ($key % 2 == 0)  $weight['supplier_id'] = 1;
			else  $weight['supplier_id'] = null;
			$data[] = $weight;
		}
		PropertyWeighting::insert($data);
    }
}
