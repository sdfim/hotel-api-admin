<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyWeighting;
use Illuminate\Database\Seeder;

class PropertyWeightingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $giataIds = Property::where('city', 'New York')->pluck('code')->all();
        $issetIds = PropertyWeighting::whereIn('property', $giataIds)->pluck('property')->all();
        $today = now();
        $data = [];

        foreach ($giataIds as $key => $giataId) {
            if (in_array($giataId, $issetIds)) {
                continue;
            }
            $weight['property'] = $giataId;
            $weight['weight'] = rand(1, 10000);
            if ($key % 2 == 0) {
                $weight['supplier_id'] = 1;
            } else {
                $weight['supplier_id'] = null;
            }
            $weight['created_at'] = $today;
            $weight['updated_at'] = $today;
            $data[] = $weight;
        }
        PropertyWeighting::insert($data);
    }
}
